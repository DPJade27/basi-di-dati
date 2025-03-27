<?php
ob_start();
    /*
    edit_blog consente a un utente autenticato di modificare i dettagli di un blog esistente, come il titolo, la descrizione e aggiungere coautori
    - verifica che l'utente autenticato sia il proprietario del blog 
    - carica i dettagli attuali del blog per la modifica (titolo e descrizione)
    - permette agli utenti di aggiornare il titolo e la descrizione del blog
    - menu a tendina per aggiungere 1 o più coautori al blog
    - valida gli input e aggiorna il database con le modifiche apportate
    */

// Connessione al db
include('db_connect.php');
include('header.php');


//check user
if (!isset($_SESSION['username'])) {
    die("Unauthorized access. Please log in.");
}

$ownerUsername = mysqli_real_escape_string($conn, $_SESSION['username']);
$blog = [];
$errors = [];

//fetch blog data
if (isset($_GET['id'])) {

    $blogId = mysqli_real_escape_string($conn, $_GET['id']);
    //recupero info di un blog specifico (titolo, descrizione, proprietario e coautor(S))
    $sqlFetchBlog = "SELECT blog_.id, blog_.title, blog_.description, blog_.id_user, user.username AS owner_name,
                        GROUP_CONCAT(coauthor.username SEPARATOR ', ') AS coauthors
                    FROM blog_
                    INNER JOIN user ON blog_.id_user = user.id
                    LEFT JOIN blog_coauthor ON blog_.id = blog_coauthor.id_blog
                    LEFT JOIN user AS coauthor ON blog_coauthor.id_user = coauthor.id
                    WHERE blog_.id = '$blogId'
                    GROUP BY blog_.id";

    $result = mysqli_query($conn, $sqlFetchBlog);

    if ($result && mysqli_num_rows($result) > 0) {
        $blog = mysqli_fetch_assoc($result);

        // check se è il proprietario
        if ($ownerUsername !== $blog['owner_name']) {
            die("You are not authorized to edit this blog.");
        }
    } else {
        die("Blog not found.");
    }

    mysqli_free_result($result);
} else {
    die("Invalid blog ID.");
}


//fetch user per il coauthor dropdown
$sqlFetchUsers = "SELECT username FROM user";
$resultUsers = mysqli_query($conn, $sqlFetchUsers);
$users = [];
if ($resultUsers && mysqli_num_rows($resultUsers) > 0) {
    $users = mysqli_fetch_all($resultUsers, MYSQLI_ASSOC);
}


//tasto edit
if (isset($_POST['edit_blog_sub'])) {

    $blogId = mysqli_real_escape_string($conn, $_POST['blog_id'] ?? '');
    if (empty($blogId)) {
        die("Error: Missing blog ID.");
    }

    $newTitle = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $newDescription = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $coauthorUsername = mysqli_real_escape_string($conn, $_POST['coauthor'] ?? '');

    $updateFields = [];
    if (!empty($newTitle)) {
        if (!preg_match('/^[ A-Za-z]+$/', $newTitle)) {
            $errors['title'] = '<p>Title must only contain letters and spaces.</p>';
        } else {
            $updateFields[] = "title = '$newTitle'";
        }
    }

    if (!empty($newDescription)) {
        $updateFields[] = "description = '$newDescription'";
    }

    if (!empty($updateFields)) {
        $updateFieldsString = implode(", ", $updateFields); //unisce gli elementi dell'array in una stringa, separandoli con una virgola e uno spazio
        $updateBlogQuery = "UPDATE blog_ SET $updateFieldsString WHERE id = '$blogId' AND id_user = (SELECT id FROM user WHERE username = '$ownerUsername')";
        if (!mysqli_query($conn, $updateBlogQuery)) {
            $errors['database'] = '<p>Error updating blog: ' . mysqli_error($conn) . '</p>';
        }
    }


    //coauthor
    $allCoauthorsCSV = trim($_POST['coauthors_csv'] ?? ''); //trim() elimina spazi bianchi all'inizio e alla fine di una stringa

    if (!empty($allCoauthorsCSV)) {
        $allCoauthorsArray = explode(',', $allCoauthorsCSV); //divide una stringa in un array, usa virgola come delimitatore

        //per ogni coauthore, inserisci nel blog_coauthor 
        foreach ($allCoauthorsArray as $coauthorUsername) {
            $coauthorUsername = trim($coauthorUsername);
            if ($coauthorUsername === '') continue; //itera su un array di nomi di coautori e pulisce i dati

            //cerca user id
            $fetchUserIdQuery = "SELECT id FROM user WHERE username = '$coauthorUsername'";

            $userIdResult = mysqli_query($conn, $fetchUserIdQuery);
            if ($userIdResult && mysqli_num_rows($userIdResult) > 0) {
                $coauthorId = mysqli_fetch_assoc($userIdResult)['id'];

                //inserisce coauthr, ignora duplicati
                $addCoauthorQuery = "INSERT INTO blog_coauthor (id_blog, id_user) 
                                    VALUES ('$blogId', '$coauthorId') 
                                    ON DUPLICATE KEY UPDATE id_blog = id_blog";
                if (!mysqli_query($conn, $addCoauthorQuery)) {
                    $errors['coauthor'] = '<p>Error adding coauthor ' 
                                          . htmlspecialchars($coauthorUsername) . ': ' 
                                          . mysqli_error($conn) . '</p>';
                }
            } else {
                $errors['coauthor'] = '<p>Coauthor username not found: ' 
                                      . htmlspecialchars($coauthorUsername) . '</p>';
            }
        }
    }

    //redirect se success
    if (!array_filter($errors)) {
        header("Location: myblogs.php");
        exit();
    }

}

//chiudo connessione
mysqli_close($conn);

?>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        input[type="file"] {
            display: none;
        }

        .custom-dropdown {
            position: absolute;
        }

        .hidden {
            display: none;
        }
    </style>
    
</head>

<body class="bg-black text-white">

    <!-- back button -->
    <div class="flex justify-start w-full px-5 py-4">
    <a href="myblogs.php?username=<?php echo $username; ?>" 
       class="inline-flex items-center text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
             class="w-5 h-5 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to My Blogs
    </a>
    </div>

    <!-- edit blog form -->
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4">
        <h2 class="text-2xl font-semibold mb-4 text-center text-white">Edit Blog</h2>

        <div class="space-y-4">


            <form id="edit_blog" method="post" action="edit_blog.php?id=<?php echo $blog['id']; ?>" class="space-y-2 bg-gray-800">
                <input type="hidden" name="blog_id" value="<?php echo htmlspecialchars($blog['id']); ?>">

                <!-- title -->
                <label for="title" class="block text-sm font-medium text-gray-200">Edit Title:</label>
                <input type="text" id="title" name="title" 
                    value="<?php echo htmlspecialchars($blog['title']); ?>" 
                    class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>

                 <!-- description -->
                <label for="description" class="block text-sm font-medium text-gray-200">Edit Description:</label>
                <textarea id="description" name="description" rows="4"
                        class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required><?php echo htmlspecialchars($blog['description']); ?></textarea>


                <!-- dropdown coauthors -->
                <div class="relative">
                    <button id="dropdownCoauthorButton" type="button"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 
                            focus:outline-none focus:ring-blue-300 font-medium rounded-lg 
                            text-sm px-5 py-2.5 inline-flex items-center dark:bg-blue-600 
                            dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        <div id="dropdown_coauthor_selected">Add Coauthor(s)</div>
                        <svg class="w-2.5 h-2.5 ml-2" aria-hidden="true" 
                            xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" 
                                stroke-linejoin="round" stroke-width="2" 
                                d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    <!-- dropdown menu -->
                    <div id="dropdown_coauthor"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg 
                                shadow w-44 dark:bg-gray-700 custom-dropdown mt-2">
                        <ul id="select_coauthor" 
                            class="py-2 text-sm text-gray-700 dark:text-gray-200">
                            <?php foreach ($users as $usr): ?>
                                <li>
                                    <a href="#" 
                                    class="block px-4 py-2 hover:bg-gray-100 
                                            dark:hover:bg-gray-600 dark:hover:text-white"
                                    onclick="addCoauthor('<?php echo htmlspecialchars($usr['username']); ?>')">
                                        <?php echo htmlspecialchars($usr['username']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- input nascosto per store dei coauthors -->
                <input type="hidden" id="coauthors_csv" name="coauthors_csv" value="">

                <!-- submit button -->
                <button type="submit" name="edit_blog_sub"
                        class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium focus:outline-none">
                    Save Changes
                </button>

            </form>
        </div>
    </div>
</body>

<script src="../js/jquery.js"></script>
<script type="text/javascript">
    const selectedCoauthors = [];

//quando un user sceglie un coauthor dal dropdown
function addCoauthor(username) {
    //evita duplicati
    if (!selectedCoauthors.includes(username)) {
        selectedCoauthors.push(username);
    }
    //update dei label visibili e input nascosti
    updateCoauthorsUI();
}

//refresh il laber e input nascosto 
function updateCoauthorsUI() {
    const label = document.getElementById('dropdown_coauthor_selected');
    const hiddenField = document.getElementById('coauthors_csv');

    //mostra i coauthors separati da una virgola
    const coauthorsStr = selectedCoauthors.join(', ');
    label.textContent = coauthorsStr || 'Add Coauthor(s)';
    
    //store separati da virgola senza spazio 
    hiddenField.value = selectedCoauthors.join(',');
}

//toggle the dropdown
document.addEventListener('DOMContentLoaded', () => {
    const dropdownButton = document.getElementById('dropdownCoauthorButton');
    const dropdownMenu   = document.getElementById('dropdown_coauthor');

    dropdownButton.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle('hidden');
    });

    //chiude dropdown se user clikka fuori
    document.addEventListener('click', (event) => {
        if (!dropdownMenu.contains(event.target) 
            && !dropdownButton.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});
</script>


</body>
</html>