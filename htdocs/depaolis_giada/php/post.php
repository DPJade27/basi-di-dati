<?php
/*
post.php permette di visualizzare i dettagli di un blog specifico, incluso il titolo, l'autore, i coautori e i post associati 
gli utenti loggati che sono proprietari del blog o coautori possono creare nuovi post e caricare immagini relative al blog 

- visualizzazione delle informazioni del blog (titolo, autore, coautori)
- creazione di nuovi post (solo per il proprietario o coautori)
- visualizzazione dei post pubblicati con contenuti, immagini e informazioni sull'autore
- interazione con i post tramite pulsanti "Mi piace" e "Commenti"
- gestione delle immagini dei post e dei commenti associati
- possibilità di eliminare post/commenti per gli utenti autorizzati (user e coauthor)

La pagina utilizza chiamate AJAX per gestire le azioni "Mi piace" e l'aggiunta di commenti senza ricaricare l'intera pagina.
*/

ob_start();
include('db_connect.php');
include('header.php');

//check session user
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    $username = null; //anche se non si è loggati si può visualizzare i post
}

//se user è loggato, get user id
$id_user = null;
if ($username) {
    $sqlUser = "SELECT id FROM user WHERE username = '$username'";
    $risUser = mysqli_query($conn, $sqlUser);
    if ($risUser && mysqli_num_rows($risUser) > 0) {
        $id_user = mysqli_fetch_assoc($risUser)['id'];
    }
}

//retrieve the blog ID from GET
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_blog = mysqli_real_escape_string($conn, $_GET['id']);

    //fetch blog info + owner’s username
    $sqlBlog = " SELECT b.*, u.username AS owner_name
                FROM blog_ b
                JOIN user u ON b.id_user = u.id
                WHERE b.id = '$id_blog'";
    $risBlog = mysqli_query($conn, $sqlBlog);
    $blog = mysqli_fetch_assoc($risBlog);

    if (!$blog) {
        die('Error: Blog not found or invalid ID.');
    }

    //fetch coauthors
    $coauthors = [];
    $sqlCoauthors = "SELECT u.username
                    FROM blog_coauthor bc
                    JOIN user u ON bc.id_user = u.id
                    WHERE bc.id_blog = '$id_blog'";
    $resCoauthors = mysqli_query($conn, $sqlCoauthors);
    if ($resCoauthors && mysqli_num_rows($resCoauthors) > 0) {
        $coauthors = mysqli_fetch_all($resCoauthors, MYSQLI_ASSOC);  // an array of ['username' => '...']
    }

    //check se current user è proprietario o coauthor => se si, può creare nuovi post
    $isAuthorized = false;
    if ($id_user) {
        $isOwner = ($blog['id_user'] == $id_user);
        $isCoauthor = in_array(['username' => $username], $coauthors);
        $isAuthorized = ($isOwner || $isCoauthor);
    }

    //se un nuovo post è stato creato
    if (isset($_POST['create_post_sub'])) {
        //validazioni
        $errors = [];
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '') {
            $errors['title'] = 'Missing Title.';
        } elseif (!preg_match('/^[ A-Za-z]+$/', $title)) {
            $errors['title'] = 'Title must contain only letters and spaces.';
        }

        if ($content === '') {
            $errors['content'] = 'Missing Description.';
        }

        if (!array_filter($errors)) {
            //inserisci nuovo post
            $titleEsc   = mysqli_real_escape_string($conn, strtolower($title));
            $contentEsc = mysqli_real_escape_string($conn, strtolower($content));
            $created_at = date('Y-m-d H:i:s');

            $sqlNewPost = "INSERT INTO post_ (title, content, created_at, id_user, id_blog)
                            VALUES ('$titleEsc', '$contentEsc', '$created_at', '$id_user', '$id_blog')";
            if (mysqli_query($conn, $sqlNewPost)) {
                $newPostId = mysqli_insert_id($conn);

                //handler per una o più immagini (opzionale)
                if (!empty($_FILES['images']['name'][0])) {
                    $countFiles = count($_FILES['images']['name']);
                    $targetDir  = "../img/user_upload/";

                    for ($i = 0; $i < $countFiles; $i++) {
                        $fileName = $_FILES['images']['name'][$i];
                        $fileTmp  = $_FILES['images']['tmp_name'][$i];
                        $fileSize = $_FILES['images']['size'][$i];

                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $accepted = ['jpg', 'jpeg', 'png'];
                        if ($fileSize <= 1024 * 1024 && in_array($ext, $accepted)) {
                            $targetFile = $targetDir . basename($fileName);
                            if (move_uploaded_file($fileTmp, $targetFile)) {
                                //inserisci record in post_image
                                $sqlInsertImg = "
                                    INSERT INTO post_image (post_id, image_path)
                                    VALUES ('$newPostId', '$targetFile')
                                ";
                                mysqli_query($conn, $sqlInsertImg);
                            }
                        }
                    }
                }

                //redirect o mostra success
                header("Location: post.php?id=$id_blog");
                exit();
            } else {
                echo 'SQL Error: ' . mysqli_error($conn);
            }
        } else {
            //errors
            foreach ($errors as $err) {
                echo "<div style='color: red;'>$err</div>";
            }
        }
    }

    //fetch tutti posts per questo blog
    $sqlPosts = "SELECT p.*, u.username AS author_name
                FROM post_ p
                JOIN user u ON p.id_user = u.id
                WHERE p.id_blog = '$id_blog'
                ORDER BY p.created_at DESC";
    $resPosts = mysqli_query($conn, $sqlPosts);
    $posts    = mysqli_fetch_all($resPosts, MYSQLI_ASSOC);
    
    //se ci sono post, prendi gli id
    $postIds = array_column($posts, 'id'); // [1, 2, 3]
    $inList  = implode(',', $postIds);     // "1,2,3"
    
    //fetch imagini da post_image
    $sqlImgs = "SELECT post_id, image_path FROM post_image WHERE post_id IN ($inList)";
    $resImgs = mysqli_query($conn, $sqlImgs);
    
    $postImages = []; //creazione mappa: post_id -> [image1, image2, ...]
    if ($resImgs && mysqli_num_rows($resImgs) > 0) {
        while ($img = mysqli_fetch_assoc($resImgs)) {
            $pid = $img['post_id'];
            $path = $img['image_path'];
            $postImages[$pid][] = $path;
        }
    }

    //fetch comments
    $comments = [];
    if (!empty($postIds)) {
        $sqlComment = "SELECT c.*, u.username AS comment_author
                        FROM comment c
                        JOIN user u ON c.id_user = u.id
                        WHERE c.id_post IN ($inList)
                        ORDER BY c.created DESC";
        $resComment = mysqli_query($conn, $sqlComment);
        if ($resComment && mysqli_num_rows($resComment) > 0) {
            $comments = mysqli_fetch_all($resComment, MYSQLI_ASSOC);
        }
    }

    //fetch likes (per fare count + check se user liked post)
    $likedPosts  = []; // post IDs dove ho messo like
    $postLikeCnt = []; // post_id => number of likes

    if (!empty($postIds)) {
        //post dove user ha messo like
        if ($id_user) {
            $sqlUserLiked = "SELECT id_post 
                            FROM like_ 
                            WHERE id_user = '$id_user' 
                            AND id_post IN ($inList)";

            $resUserLiked = mysqli_query($conn, $sqlUserLiked);
            if ($resUserLiked && mysqli_num_rows($resUserLiked) > 0) {
                while ($rowLiked = mysqli_fetch_assoc($resUserLiked)) {
                    $likedPosts[] = $rowLiked['id_post'];
                }
            }
        }

        //count dei like totali
        $sqlLikeCount = "SELECT id_post, COUNT(*) AS cnt
                        FROM like_
                        WHERE id_post IN ($inList)
                        GROUP BY id_post";

        $resLikeCount = mysqli_query($conn, $sqlLikeCount);
        if ($resLikeCount && mysqli_num_rows($resLikeCount) > 0) {
            while ($lc = mysqli_fetch_assoc($resLikeCount)) {
                $postLikeCnt[$lc['id_post']] = $lc['cnt'];
            }
        }
    }

} else {
    die("Invalid blog ID.");
}

ob_end_flush();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type="file"] { display: none; }
        .hidden { display: none; }
    </style>
</head>
<body class="bg-black text-white">

<div class="px-4">
    <!-- vai a index -->
    <a href="index.php" class="flex items-center space-x-2 mt-2 mb-4 text-gray-400 hover:text-gray-300">
        <svg class="w-4 h-4" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M13 5H1M1 5l4 4M1 5l4-4"/>
        </svg>
        <span>Previous</span>
    </a>

    <!-- blog info -->
    <h1 class="text-2xl font-semibold text-gray-100">
        <?php echo htmlspecialchars($blog['title']); ?>
    </h1>
    <!-- blog owner -->
    <div class="text-sm text-gray-400 mb-2">
        Owner: <?php echo htmlspecialchars($blog['owner_name']); ?>
    </div>
    <?php if (!empty($coauthors)): ?>
        <!-- blog coauthor, se c'è -->
        <div class="text-sm text-gray-400 mb-4">
            Coauthors: 
            <?php 
            echo implode(', ', array_map(
                fn($co) => htmlspecialchars($co['username']),
                $coauthors
            )); 
            ?>
        </div>
    <?php endif; ?>

    <!-- create post form (if authorized) -->
    <?php if ($isAuthorized): ?>
        <div class="bg-gray-800 rounded-lg p-4 mb-6">
            <form method="post" action="post.php?id=<?php echo $blog['id']; ?>" enctype="multipart/form-data"
                  class="space-y-4">
                <!-- title -->
                <div>
                    <input type="text" name="title" id="title" placeholder="Title:" 
                           class="w-full p-2 rounded-lg bg-gray-700 border border-gray-600 text-white
                                  focus:ring-blue-500 focus:border-blue-500">
                </div>
                <!-- content -->
                <div>
                    <textarea name="content" id="content" rows="2" placeholder="Write your post content..."
                              class="w-full p-2 rounded-lg bg-gray-700 border border-gray-600 text-white
                                     focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <!-- crea post button -->
                <div class="flex items-center space-x-4">
                    <button type="submit" name="create_post_sub"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Create New Post
                    </button>
                    <!-- più images upload -->
                    <label class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg cursor-pointer">
                        <input id="images" type="file" name="images[]" class="hidden" multiple
                               accept="image/png,image/jpeg,image/jpg">
                        Add Images
                    </label>
                </div>
            </form>
        </div>
    <?php else: ?>
        <p class="text-gray-400 mb-6">
            You are not authorized to create a new post for this blog.
        </p>
    <?php endif; ?>

    <!-- posts list -->
    <div>
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php 
                $pid = $post['id'];
                //check se user corrente ha messo like
                $userLiked = in_array($pid, $likedPosts);
                //conto like totali
                $likeCount = $postLikeCnt[$pid] ?? 0;
                ?>
                <div class="bg-gray-800 rounded-lg p-4 mb-6">
                    <div class="flex justify-between">
                        <h3 class="text-xl font-semibold text-white">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </h3>
                        <!-- remove post button (se blog owner o coauthor che ha creato il post) -->
                        <?php
                        $canDelete = false;
                        if ($id_user) {
                            $isOwner    = ($blog['id_user'] == $id_user);
                            $isCoauthor = in_array(['username' => $username], $coauthors);
                            if ($isOwner || ($isCoauthor && $post['id_user'] == $id_user)) {
                                $canDelete = true;
                            }
                        }
                        if ($canDelete):
                        ?>
                            <button class="text-red-400 hover:text-red-300 text-sm"
                                    onclick="delete_post(<?php echo $post['id']; ?>)">
                                Remove
                            </button>
                        <?php endif; ?>
                    </div>
                    <!-- post author -->
                    <div class="text-sm text-gray-400">
                        Author: <?php echo htmlspecialchars($post['author_name']); ?>
                    </div>
                    <!-- created at -->
                    <div class="text-sm text-gray-400 mb-2">
                        Created: <?php echo htmlspecialchars($post['created_at']); ?>
                    </div>

                    <!-- post content -->
                    <p class="text-gray-300 mb-4">
                        <?php echo htmlspecialchars($post['content']); ?>
                    </p>

                    <!-- images (se ci sono) -->
                    <?php if (!empty($postImages[$pid])): ?>
                        <div class="flex flex-wrap gap-4 mb-4">
                            <?php foreach ($postImages[$pid] as $imagePath): ?>
                                <div>
                                    <img 
                                        src="<?php echo htmlspecialchars($imagePath); ?>"
                                        alt="Post Image"
                                        class="rounded-lg max-w-xs h-auto" 
                                    />
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- like & comment buttons -->
                    <div class="flex items-center space-x-4 mb-2">
                        <!-- like -->
                        <?php if ($id_user): ?>
                            <button class="like-button flex items-center space-x-1 
                                           text-gray-300 hover:text-white"
                                    data-id-post="<?php echo $pid; ?>"
                                    data-is-liked="<?php echo $userLiked ? 'true' : 'false'; ?>">
                                <?php 
                                //heart icon
                                $fill = $userLiked ? 'currentColor' : 'none';
                                ?>
                                <svg class="w-5 h-5" fill="<?php echo $fill; ?>" 
                                     stroke="currentColor" 
                                     stroke-width="1.5" 
                                     viewBox="0 0 24 24" 
                                     xmlns="http://www.w3.org/2000/svg"
                                     aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 20.25c-.62-.47-1.17-.92-1.66-1.32C6.91 15.52 4 13.08 4 10.21 4 8.21 5.42 6 7.58 6c1.25 0 2.45.57 3.42 1.48A5.06 5.06 0 0 1 14.42 6C16.58 6 18 8.21 18 10.21c0 2.87-2.91 5.31-6.34 8.72-.49.4-1.04.85-1.66 1.32Z"/>
                                </svg>
                                <span class="like-count text-sm">
                                    <?php echo $likeCount; ?>
                                </span>
                            </button>
                        <?php else: ?>
                            <!-- se non loggato, vedo solo il count dei like ma non il bottone -->
                            <div class="flex items-center space-x-1 text-gray-500">
                                <svg class="w-5 h-5" fill="none" 
                                     stroke="currentColor" stroke-width="1.5"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                     aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 20.25c-.62-.47-1.17-.92-1.66-1.32C6.91 15.52 4 13.08 4 10.21 4 8.21 5.42 6 7.58 6c1.25 0 2.45.57 3.42 1.48A5.06 5.06 0 0 1 14.42 6C16.58 6 18 8.21 18 10.21c0 2.87-2.91 5.31-6.34 8.72-.49.4-1.04.85-1.66 1.32Z"/>
                                </svg>
                                <span class="text-sm"><?php echo $likeCount; ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- comment toggle button -->
                        <button class="comment-toggle flex items-center space-x-1 
                                       text-gray-300 hover:text-white"
                                data-id-post="<?php echo $pid; ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" 
                                 stroke-width="1.5" viewBox="0 0 24 24" 
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 20.25c4.97 0 9-3.13 9-7 0-3.87-4.03-7-9-7S3 9.38 3 13.25c0 1.47.53 2.84 1.46 3.96l-.42 2.27 2.33-.58c1.29.58 2.74.85 4.63.85Z"/>
                            </svg>
                            <span class="text-sm">Comments</span>
                        </button>
                    </div>

                    <!-- comments container (nascosto inizialmente) -->
                    <div class="comments-container hidden" data-post-id="<?php echo $pid; ?>">
                        <!-- lista tutti commenti per questo post -->
                        <div class="space-y-2 mb-2">
                            <?php 
                            foreach ($comments as $c) {
                                if ($c['id_post'] == $pid) {
                                    ?>
                                    <div class="border-b border-gray-700 pb-2">
                                        <!-- author commento -->
                                        <span class="text-sm text-gray-300">
                                            <?php echo htmlspecialchars($c['comment_author']); ?>
                                        </span>
                                        <!-- created at -->
                                        <span class="text-xs text-gray-500 ml-2">
                                            <?php echo date_format(new DateTime($c['created']), 'd M Y H:i:s'); ?>
                                        </span>
                                        <!-- testo -->
                                        <p class="ml-4 text-gray-300">
                                            <?php echo htmlspecialchars($c['text']); ?>
                                        </p>

                                        <!-- se user loggato è autore del commento, vede il tasto delete -->
                                        <?php if ($id_user && $id_user == $c['id_user']): ?>
                                            <button class="text-red-500 text-sm hover:underline"
                                                    onclick="delete_comment(<?php echo $c['id']; ?>)">
                                                Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <!-- form nuovo commento, solo per user loggati -->
                        <?php if ($id_user): ?>
                            <form class="new-comment-form" data-post-id="<?php echo $pid; ?>">
                                <textarea class="new-comment-content w-full mb-1 p-2 
                                               bg-gray-700 border border-gray-600 text-white 
                                               rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                          rows="2" placeholder="Add a comment..."></textarea>
                                <button type="button" 
                                        class="submit-comment-button px-4 py-1 
                                               bg-blue-600 hover:bg-blue-700 text-white 
                                               rounded-lg">
                                    Comment
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-gray-500">Log in to comment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-400">No posts found for this blog.</p>
        <?php endif; ?>
    </div>
</div>

<script src="../js/jquery.js"></script>
<script>
function delete_post(id_post) {
    if (confirm('Are you sure you want to delete this post?')) {
        window.location.href = 'delete_post.php?id=' + id_post;
    }
}

//toggle comment container
$(document).ready(function() {
    $('.comment-toggle').on('click', function() {
        const pid = $(this).data('id-post');
        const container = $(`.comments-container[data-post-id='${pid}']`);
        container.toggleClass('hidden');
    });
});

//submit nuovo commento
$(document).on('click', '.submit-comment-button', function() {
    const form = $(this).closest('.new-comment-form');
    const pid  = form.data('post-id');
    const textarea = form.find('.new-comment-content');
    const commentText = textarea.val().trim();

    if (!commentText) {
        alert('Comment cannot be empty.');
        return;
    }

    // invia tramite AJAX a un endpoint separato: new_comment.php
    // aggiorna e ricarica i commenti
    $.post('new_comment.php', {
        idPost: pid,
        comment: commentText
    }).done(function(resp) {
        if (resp === 'success') {
            location.reload(); 
        } else {
            alert('Failed to add comment: ' + resp);
        }
    }).fail(function() {
        alert('Error adding comment');
    });
});

//like/unlike post
$(document).on('click', '.like-button', function() {
    const btn       = $(this);
    const pid       = btn.data('id-post');
    const isLiked   = (btn.data('is-liked') === true || btn.data('is-liked') === 'true');
    const likeCountEl = btn.find('.like-count');
    let currentCount = parseInt(likeCountEl.text()) || 0;

    //invia tramite AJAX a un endpoint separato: like_post.php
    $.post('like_post.php', {
        idPost: pid,
        action: isLiked ? 'unlike' : 'like'
    }).done(function(resp) {
        //resp deve ritornare success/fail
        if (resp === 'success-like') {
            //user mette like
            btn.data('is-liked','true');
            btn.find('svg').attr('fill','currentColor');
            likeCountEl.text(currentCount + 1);
        } else if (resp === 'success-unlike') {
            //user toglie like
            btn.data('is-liked','false');
            btn.find('svg').attr('fill','none');
            likeCountEl.text(currentCount - 1);
        } else {
            alert('Like action failed: ' + resp);
        }
    }).fail(function() {
        alert('Failed to send like request');
    });
});
</script>

<script>
//delete commento
function delete_comment(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        window.location.href = 'delete_comment.php?idComment=' + commentId;
    }
}
</script>


</body>
</html>
