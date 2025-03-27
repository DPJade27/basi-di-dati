<?php

    /*
     * La pagina index è la prima pagina che compare a chiunque visiti il sito.
     * 
     * mostra un elenco dei blog creati da tutti gli utenti, con titolo, descrizione, proprietario, categoria, sottocagoria, e data creazione
     * ricerca dei blog: searchbar che permette di cercare i blog in base al titolo o alla categoria 
     * eliminazione dei blog: gli utenti loggati possono eliminare solo i propri blog 
     * edit dei blog: gli utenti loggati possono editare i propri blog (titolo, descrizione) e aggiungere un coautore
     * cliccando sul titolo di ciascun blog è possibile visualizzare tutti i post di quel blog
     * gli utenti non loggati vedranno comunque tutti i blog esistenti
     */

    //connessione al db
    include('db_connect.php');
    include('header.php');

    $username = '';
    
    //check se l'username è settato 
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else {
        $username = null; //visualizzazione index anche se non si è loggati
    }

    //query che recupera i dati completi dei blog, inclusi titolo, descrizione, autore, categoria, sottocategoria, immagine e data di creazione
    //usa JOIN per unire tabelle correlate (categorie, sottocategorie, coautori) e GROUP_CONCAT per aggregare i coautori in una singola stringa
    //risultati sono raggruppati per ID del blog e ordinati per data di creazione in ordine decrescente.
    $sqlGetBlogData = "SELECT blog_.id AS blog_id, blog_.title, blog_.description, 
                          blog_.id_user, blog_.created_at, blog_.image, category.name AS category_name, 
                          user.username AS owner_name, subcategory.name AS subcategory_name,
                          GROUP_CONCAT(coauthor.username SEPARATOR ', ') AS coauthors
                        FROM blog_
                        INNER JOIN category ON category.id = blog_.id_category 
                        INNER JOIN user ON user.id = blog_.id_user
                        LEFT JOIN subcategory ON subcategory.id = blog_.id_subcategory
                        LEFT JOIN blog_coauthor ON blog_.id = blog_coauthor.id_blog
                        LEFT JOIN user AS coauthor ON blog_coauthor.id_user = coauthor.id
                        GROUP BY blog_.id
                        ORDER BY blog_.created_at DESC"; 
    
    //sql category
    $sqlCategory = "SELECT * FROM category";

    //row result
    $resultBlogData = mysqli_query($conn, $sqlGetBlogData);
    $resultCategory = mysqli_query($conn, $sqlCategory);

    //rows fetchate in array
    $blogs = mysqli_fetch_all($resultBlogData, MYSQLI_ASSOC);
    $categories = mysqli_fetch_all($resultCategory, MYSQLI_ASSOC);

    //libero memoria
    mysqli_free_result($resultBlogData);
    mysqli_free_result($resultCategory);

    mysqli_close($conn);


?>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
    
<body class="bg-black text-white">
    
    <!-- search bar-->
    <form class="pl-4 pr-4">
        <label for="blogsearch" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                </svg>
            </div>
            <input type="search" id="blogsearch"
                class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="Search Blogs by Title or Category..." required />
            </div>
    </form>

    <!-- blog list -->
    <div id="blog_list" class="space-y-4 p-4">

        <?php foreach ($blogs as $blog) { ?>

            <!-- prendo dinamicamente ogni blog -->
            <div id="<?php echo $blog['blog_id']; ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <!-- blog title -->   
                <div class="flex justify-between items-center mb-2">
                    <div class="text-xl font-semibold text-gray-900 dark:text-white cursor-pointer hover:text-blue-600"
                        onclick="window.location.href='post.php?id=<?php echo $blog['blog_id']; ?>'"> <!-- se clikko sul titolo apro il blog e vedo tutti i post di quel blog -->
                        <?php echo ucfirst(htmlspecialchars($blog['title'])); ?>
                    </div>

                    <!-- remove and edit button -> visibili al proprietario e no al coauthor -->
                    <?php if (!empty($username) && ($username === $blog['owner_name'])): ?>
                        <div class="flex space-x-4"> 
                            <!-- edit button -->
                            <div class="text-blue-500 text-sm cursor-pointer hover:underline"
                                onclick="window.location.href='edit_blog.php?id=<?php echo $blog['blog_id']; ?>'">
                                Edit
                            </div>
                            <!-- remove button -->
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

                <!-- coauthors, se ci sono -->
                <?php if (!empty($blog['coauthors'])): ?>
                    <div id="list_coauthors" class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Coauthors: <?php echo htmlspecialchars($blog['coauthors']); ?>
                    </div>
                <?php endif; ?>

                <!-- category -->
                <div id="list_category" class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Category: <?php echo htmlspecialchars($blog['category_name']); ?>
                </div>

                <!-- subcategory, se esiste -->
                <?php if (!empty($blog['subcategory_name'])): ?>
                    <div id="list_subcategory" class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Subcategory: <?php echo htmlspecialchars($blog['subcategory_name']); ?>
                    </div>
                <?php endif; ?>

                <!-- data creazione -->
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
                        <img src="<?php echo htmlspecialchars($blog['image']); ?>" 
                            alt="<?php echo htmlspecialchars($blog['title']); ?> Image" 
                            class="rounded-lg max-w-xs h-auto">
                    </div>
                <?php endif; ?>
            </div>

        <?php } ?>
    </div>
</body>

<!-- ricerca blog per titolo e categoria jquery -->
<script src="../js/jquery.js"></script>
<script type="text/javascript">

    //al caricamento dello script mi salvo la lista dei blog caricata
    blog_list = <?php echo json_encode($blogs) ?>;
    
    /**
     * listener evento keyup su text box per ricerca blog per titolo
     * usato per rimuovere e aggiungere elementi html blog dal container di blog in base al loro titolo e category
     */
    $(document).ready(function () {

        $('#blogsearch').keyup(function () {

            //assegno ad una variabile il testo scritto nel box di ricerca
            const blogsearch = $(this).val().toLowerCase();

            //ciclo tutti i blog html che sono nel dom
            $('#blog_list').children().each(function () {
                
                const idBlogDiv = this['id'];

                //recupero l'oggetto blog a partire dall'id del blog html sul quale sto ciclando
                const blog_object = blog_list.find(function (blog) {
                    return blog['blog_id'] === idBlogDiv;
                });

                //se il campo di ricerca è vuoto mostro tutti i div dei blog
                if(!blogsearch || blogsearch.length === 0) {
                    this['hidden'] = false;
                } else if (blog_object) { //in caso contrario controllo se il titolo del blog contiene la stringa cercata
                    
                    const title_match = blog_object['title'].toLowerCase().includes(blogsearch);
                    const category_match = blog_object['category_name'].toLowerCase().includes(blogsearch);

                    //mostra il blog id se matcha con il title o category
                    this['hidden'] = !(title_match || category_match);

                }
            });
        });
    });

</script>

<!-- delete blog script -->
<script>
    function delete_blog(blogId) {
        if (confirm('Are you sure you want to delete this blog?')) {
            //redirect al handler php
            window.location.href = `delete_blog.php?id=${blogId}`;
        }
    }
</script>




