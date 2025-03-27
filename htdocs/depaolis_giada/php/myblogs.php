<?php

    /*
    My Blogs è la pagina dove un utente loggato può visualizzare la lista dei blog creati, modificarli, eliminarli e crearne di nuovi
    e i blog nei quali sono coautori
    Gli utenti possono:
    - Creare nuovi blog (se premium o se hanno creato meno di 3 blog)
    - Modificare o eliminare i blog che possiedono
    - Visualizzare i dettagli dei blog, inclusi titolo, descrizione, immagine, categoria, sottocategoria, coautori e data di creazione
    - Accedere ai blog nei quali sono coautori, dove potranno solo creare post e eliminarli
*/

    //connessione al db
    include('db_connect.php');
    include('header.php');

    $num_blog = 0;
    $is_premium = false;
    $blogs = [];

    $username = $_SESSION['username'];

    //recupero id user e status premium
    $sqlUser = "SELECT id, is_premium FROM user WHERE username = '$username'";
    $resPremium = mysqli_query($conn, $sqlUser);

    if ($resPremium) {
        $row = mysqli_fetch_assoc($resPremium);
        $id_user = $row['id'] ?? null;
        $is_premium = $row['is_premium'] ?? false;
    }

    /*blog creati dall'utente loggato
    *recupera i dettagli dei blog creati dall'utente, inclusi titolo, descrizione, categoria, sottocategoria, 
    *proprietario, coautori e immagine, ordinati per data di creazione
    *
    *GROUP_CONCAT combina i nomi degli eventuali coautori di un blog in un'unica stringa separata da virgole, 
    *così da poterli mostrare in un'unica riga
    *
    *LEFT JOIN include tutte le righe della tabella a sinistra se non c'è corrispondenza, i campi della tabella a destra saranno NULL
    *LEFT JOIN subcategory: recupera la sottocategoria del blog se esiste, se il blog non ha una sottocategoria il valore è NULL
    *LEFT JOIN blog_coauthor e LEFT JOIN user AS coauthor: associa i coautori al blog, se il blog non ha coautori i valori sono NULL
    */
    $sqlGetData = "SELECT blog_.id AS blog_id, 
                        blog_.title, 
                        blog_.description, 
                        blog_.id_user,
                        blog_.image,  
                        blog_.created_at, 
                        category.name AS category_name, 
                        user.username AS owner_name, 
                        subcategory.name AS subcategory_name,
                        GROUP_CONCAT(coauthor.username SEPARATOR ', ') AS coauthors
                    FROM blog_
                    INNER JOIN category ON category.id = blog_.id_category
                    INNER JOIN user ON user.id = blog_.id_user
                    LEFT JOIN subcategory ON subcategory.id = blog_.id_subcategory
                    LEFT JOIN blog_coauthor ON blog_.id = blog_coauthor.id_blog
                    LEFT JOIN user AS coauthor ON blog_coauthor.id_user = coauthor.id
                    WHERE blog_.id_user = $id_user
                    GROUP BY blog_.id
                    ORDER BY blog_.created_at DESC";
    
    //blogs dove l'utente loggato è coauthor, not owner (simile alla precedente)
    $sqlGetCoauthoredBlogs = "SELECT blog_.id AS blog_id, 
                                blog_.title, 
                                blog_.description, 
                                blog_.id_user, 
                                blog_.image, 
                                blog_.created_at, 
                                category.name AS category_name, 
                                user.username AS owner_name, 
                                subcategory.name AS subcategory_name,
                                GROUP_CONCAT(coauthor.username SEPARATOR ', ') AS coauthors
                            FROM blog_
                            INNER JOIN category ON category.id = blog_.id_category
                            INNER JOIN user ON user.id = blog_.id_user
                            LEFT JOIN subcategory ON subcategory.id = blog_.id_subcategory
                            LEFT JOIN blog_coauthor ON blog_.id = blog_coauthor.id_blog
                            LEFT JOIN user AS coauthor ON blog_coauthor.id_user = coauthor.id
                            WHERE blog_coauthor.id_user = $id_user AND blog_.id_user != $id_user
                            GROUP BY blog_.id
                            ORDER BY blog_.created_at DESC";


    //sql category
    $sqlCategory = "SELECT * FROM category";
        
    $result = mysqli_query($conn, $sqlGetData);
    $coauthoredResult = mysqli_query($conn, $sqlGetCoauthoredBlogs);
    $resultCategory = mysqli_query($conn, $sqlCategory);

    //check number of blogs
    if($result) {
        $num_blog = mysqli_num_rows($result);
        $blogs = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Error fetching blogs: " . mysqli_error($conn);
    }

    //fetch blogs dove user è coauthor
    if ($coauthoredResult) {
        $coauthoredBlogs = mysqli_fetch_all($coauthoredResult, MYSQLI_ASSOC);
    }

    //get categories
    if($resultCategory) {
        $categories = mysqli_fetch_all($resultCategory, MYSQLI_ASSOC);
    }


    //free memory
    mysqli_free_result($result);
    mysqli_free_result($coauthoredResult);

    mysqli_close($conn);

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blogs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
    
<body class="bg-black text-white">
    
    <?php //create blog button only for users who posted <3 blogs or premium
        if ($is_premium || $num_blog < 3) { ?>
            <!-- create blog button -->
            <div class="flex flex-row mt-1">
                <button onclick="window.location.href='create_blog.php'" 
                    class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none 
                    focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ml-4 dark:bg-blue-600 
                    dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
                    Create a New Blog
                </button>
            </div>
        <?php } else { ?>
            <!-- messaggio user che non possono più creare blogs -->
            <div class="bg-yellow-500 text-white p-4 rounded mt-2">
                <p>You can’t create more blogs.</p>
                <p><a href="premium.php" class="underline hover:text-gray-200">Go Premium</a> to unlock unlimited blog creation!</p>
            </div>
        <?php } ?>

    <!-- blog list -->
    <div id="blog_list" class="space-y-4 p-4">

        <?php if (empty($blogs)): ?>
            <p class="text-gray-500 dark:text-gray-400">No blogs found.</p>
            <!-- mex per user not premium -->
            <?php if (!$is_premium): ?>
                <p class="text-yellow-500 dark:text-yellow-400 mb-4">
                    If you're not a premium user, you can only create up to 3 blogs. 
                    <a href="premium.php" class="underline hover:text-yellow-300">Go Premium</a> to unlock unlimited blog creation!
                </p>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($blogs as $blog): ?>
                <div id="<?php echo $blog['blog_id']; ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <!-- title -->  
                <div class="flex justify-between items-center mb-2">
                    <div class="text-xl font-semibold text-gray-900 dark:text-white cursor-pointer hover:text-blue-600"
                        onclick="window.location.href='post.php?id=<?php echo $blog['blog_id']; ?>'">
                        <?php echo ucfirst(htmlspecialchars($blog['title'])); ?>
                    </div>

                    <!-- edit e remove buttons -->
                    <div class="flex space-x-2">
                        <!-- remove and edit button -> visibili al proprietario -->
                        <?php if ($username === $blog['owner_name']): ?>
                            <div class="flex space-x-4"> 
                                <!-- edit -->
                                <div class="text-blue-500 text-sm cursor-pointer hover:underline"
                                    onclick="window.location.href='edit_blog.php?id=<?php echo $blog['blog_id']; ?>'">
                                    Edit
                                </div>
                                <!-- remove -->
                                <div class="text-red-500 text-sm cursor-pointer hover:underline" 
                                    onclick="delete_blog(<?php echo $blog['blog_id']; ?>)">
                                    Remove
                                </div>                            
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- owner -->
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Owner: <?php echo htmlspecialchars($blog['owner_name']); ?>
                </div>

                <!-- coauthor, se c'è -->
                <?php if (!empty($blog['coauthors'])): ?>
                    <div id="list_coauthors" class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Coauthors: <?php echo htmlspecialchars($blog['coauthors']); ?>
                    </div>
                <?php endif; ?>

                <!-- category -->
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Category: <?php echo htmlspecialchars($blog['category_name']); ?>
                </div>

                <!-- subategory -->
                <?php if (!empty($blog['subcategory_name'])): ?>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Subcategory: <?php echo htmlspecialchars($blog['subcategory_name']); ?>
                    </div>
                <?php endif; ?>

                <!-- created at -->
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Created: <?php echo htmlspecialchars($blog['created_at']); ?>
                </div>

                <!-- description -->
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    <?php echo htmlspecialchars($blog['description']); ?>
                </p>

                <!-- blog image -->
                <?php if (!empty($blog['image'])): ?>
                    <div class="mb-4">
                        <img 
                            src="<?php echo htmlspecialchars($blog['image']); ?>" 
                            alt="<?php echo htmlspecialchars($blog['title']); ?> Image"
                            class="rounded-lg max-w-xs h-auto"
                        />
                    </div>
                <?php endif; ?>

                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    

        <!-- blogs where the user is a coauthor -->
        <h2 class="text-lg font-semibold text-gray-300 mt-6">Blogs You Coauthored</h2>
        <?php if (empty($coauthoredBlogs)): ?>
            <p class="text-gray-500 dark:text-gray-400">You are not a coauthor on any blogs yet.</p>
        <?php else: ?>
            <?php foreach ($coauthoredBlogs as $blog): ?>
                <div id="<?php echo $blog['blog_id']; ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-xl font-semibold text-gray-900 dark:text-white cursor-pointer hover:text-blue-600"
                            onclick="window.location.href='post.php?id=<?php echo $blog['blog_id']; ?>'">
                            <?php echo ucfirst(htmlspecialchars($blog['title'])); ?>
                        </div>

                        <!-- remove and edit button -> visibili al proprietario e no al coauthor -->
                        <?php if ($username === $blog['owner_name'] ): ?>
                            <div class="flex space-x-4">
                                <!-- edit  -->
                                <div class="text-blue-500 text-sm cursor-pointer hover:underline"
                                    onclick="window.location.href='edit_blog.php?id=<?php echo $blog['blog_id']; ?>'">
                                    Edit
                                </div>
                                <!-- remove -->
                                <div class="text-red-500 text-sm cursor-pointer hover:underline" 
                                    onclick="delete_blog(<?php echo $blog['blog_id']; ?>)">
                                    Remove
                                </div>                            
                            </div>
                        <?php endif; ?>

                    </div>
                    <!-- owner -->
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Owner: <?php echo htmlspecialchars($blog['owner_name']); ?>
                    </div>
                    <!-- coauthor -->
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Coauthors: <?php echo htmlspecialchars($blog['coauthors'] ?? 'None'); ?>
                    </div>
                    <!-- category -->
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Category: <?php echo htmlspecialchars($blog['category_name']); ?>
                    </div>

                    <!-- subategory -->
                    <?php if (!empty($blog['subcategory_name'])): ?>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                            Subcategory: <?php echo htmlspecialchars($blog['subcategory_name']); ?>
                        </div>
                    <?php endif; ?>
                    <!-- created at -->
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Created: <?php echo htmlspecialchars($blog['created_at']); ?>
                    </div>
                    <!-- description -->
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <?php echo htmlspecialchars($blog['description']); ?>
                    </p>
                    <!-- image -->
                    <?php if (!empty($blog['image'])): ?>
                        <div class="mb-4">
                            <img 
                                src="<?php echo htmlspecialchars($blog['image']); ?>" 
                                alt="<?php echo htmlspecialchars($blog['title']); ?> Image" 
                                class="rounded-lg max-w-xs h-auto"
                            >
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>


<script src="../js/jquery.js"></script>

<!-- delete blog script -->
<script>
    function delete_blog(blogId) {
        if (confirm('Are you sure you want to delete this blog?')) {
            //redirect ad un altro handler
            window.location.href = `delete_blog.php?id=${blogId}`;
        }
    }
</script>