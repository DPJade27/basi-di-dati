<?php
ob_start();
/*
Create Blog è la pagina dove un utente loggato può creare nuovi blogs
*/

// Connessione al db
include('db_connect.php');
include('header.php');

$image = $username = '';
$num_blog = 0;
$is_premium = false;

$username = $_SESSION['username'];

//verifica il numero di blog creati dall'utente loggato
$sqlNumBlog = "SELECT COUNT(*) as num_blog FROM blog_ WHERE id_user = '$username'";
$risNumBlog = mysqli_query($conn, $sqlNumBlog);
$num_blog = mysqli_fetch_assoc($risNumBlog)['num_blog'];

//recupera premium
$sqlIsPremium = "SELECT is_premium FROM user WHERE username = '$username'";
$is_premium = mysqli_fetch_assoc(mysqli_query($conn, $sqlIsPremium))['is_premium'];

if ($is_premium || $num_blog < 3) {

    $id = $title = $description = $created_at = $id_user = '';
    $category = $subcategory = '';
    $errors = [];

    //get existing categories
    $categories = [];
    $sqlCategory = "SELECT * FROM category";
    $risCategory = mysqli_query($conn, $sqlCategory);

    if($risCategory) {
        while ($row = mysqli_fetch_assoc($risCategory)) {
            $categories[] = $row;
        }
    } else {
        $errors[] = "Error retrieving categories: " . mysqli_error($conn);
    }


    //form submission
    if (isset($_POST['create_blog_sub'])) {

        //check title
        if (empty($_POST['title'])) {
            $errors['title'] = '<p>Missing Title</p>';
        } else {
            $title = $_POST['title'];

            if (!preg_match('/^[ A-Za-z]+$/', $title)) {
                $errors['title'] = '<p>Title must only contain letters and spaces</p>';
            }
        }

        //check description
        if (empty($_POST['description'])) {
            $errors['description'] = '<p>Missing Description</p>';
        } else {
            $description = $_POST['description'];
        }

        //image (opzionale)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $nameImageBlog = $_FILES['image']['name'];
            $nameImageBlog_tmp = $_FILES['image']['tmp_name'];
            $targetDir  = "../img/user_upload/";
            $targetFile = $targetDir . basename($nameImageBlog);
            $typeImg    = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $acceptedExtensions = ["jpg", "png", "jpeg"];

            if ($_FILES['image']['size'] > 1024 * 1024) {
                $errors['image'] = 'Image size too big. Maximum size 1MB';
            } elseif (!in_array($typeImg, $acceptedExtensions)) {
                $errors['image'] = 'Image format not accepted';
            } elseif (!move_uploaded_file($nameImageBlog_tmp, $targetFile)) {
                $errors['image'] = 'Failed to upload the image.';
            } else {
                $image = $targetFile;
            }
        }


        //check date
        $created_at = date('Y-m-d H:i:s');


        //recupero ID category selected
        if (empty($_POST['category'])) {
            $errors['category'] = '<p>Please select or add a category.</p>';
        } else {
            //user seleziona una categoria
            $category = mysqli_real_escape_string($conn, strtolower($_POST['category']));
            $id_category = null;

            //check se categoria esiste
            $sqlFindCat = "SELECT id FROM category WHERE name = '$category'";
            $resFindCat = mysqli_query($conn, $sqlFindCat);

            if($resFindCat && mysqli_num_rows($resFindCat) > 0) {
                //usa categorie esistenti
                $id_category = mysqli_fetch_assoc($resFindCat)['id'];
            } else {
                $sqlInsetCat = "INSERT INTO category (name) VALUES ('$category')";
                if (mysqli_query($conn, $sqlInsetCat)) {
                    $id_category = mysqli_insert_id($conn);
                } else {
                    $errors['category'] = "Error creating category: " . mysqli_error($conn);
                }
            }
        } 

        //recupero subcategories, sono opzionali
        $id_subcategory = null;
        if (!empty($_POST['subcategory']) && !empty($id_category)) {
            $subcategory = mysqli_real_escape_string($conn, strtolower($_POST['subcategory']));
            

            $sqlFindSub = "SELECT id FROM subcategory WHERE name = '$subcategory' AND id_category = '$id_category'";
            $resFindSub = mysqli_query($conn, $sqlFindSub);

            if($resFindSub && mysqli_num_rows($resFindSub) > 0) {
                $id_subcategory = mysqli_fetch_assoc($resFindSub)['id'];
            } else {
                $sqlInsertSub = "INSERT INTO subcategory (name, id_category) VALUES ('$subcategory', '$id_category')";
                if (mysqli_query($conn, $sqlInsertSub)) {
                    $id_subcategory = mysqli_insert_id($conn);
                } else {
                    $errors['subcategory'] = "Error creating subcategory: " . mysqli_error($conn);
                }
            }
        }


        if (!array_filter($errors)) {
            $title = mysqli_real_escape_string($conn, strtolower($_POST['title']));
            $description = mysqli_real_escape_string($conn, $_POST['description']);

            //fetch the user ID
            $sqlGetUser = "SELECT id FROM user WHERE username = '$username'";
            $resUser = mysqli_query($conn, $sqlGetUser);

            if($resUser && mysqli_num_rows($resUser) > 0) {
                $user = mysqli_fetch_assoc($resUser);
                $id_user = $user['id']; //correct user id
            } else {
                die("user not found");
            }

            $sqlNewBlog = "INSERT INTO blog_ (id, title, description, image, created_at, id_user, id_category, id_subcategory) 
                          VALUES (NULL, '$title', '$description', '$image', '$created_at', '$id_user', '$id_category'," . ($id_subcategory ?: 'NULL') . ")";


            if (mysqli_query($conn, $sqlNewBlog)) {
                $id_blog = mysqli_insert_id($conn);
                header("Location: myblogs.php?id=$id_blog");
                exit();
            } else {
                echo 'SQL Error: ' . mysqli_error($conn);
            }
        }

        foreach ($errors as $error) {
            echo $error;
        }
    }


}

ob_end_flush();
?>


<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blogs</title>
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
        #dropdownDefaultButtonSub {
        position: relative;
        z-index: 9999;
        }
    </style>
    
</head>
    
<body class="bg-black text-white min-h-screen flex flex-col">

    <!-- back button -->
    <div class="px-5 py-4">
    <a href="myblogs.php?username=<?php echo $username; ?>" 
       class="inline-flex items-center text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
             class="w-5 h-5 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to My Blogs
    </a>
    </div>

    <!-- create blog form -->
    <div class="flex-1 flex items-center justify-center">
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-2xl">
            <h2 class="text-2xl font-semibold mb-4 text-center text-white">Create a New Blog</h2>

            <div class="space-y-4">

                <!-- div che fa comparire errori trovati dal js e dal php -->
                <div id="error" class="text-red-500 my-4">
                    <?php include('errors.php'); ?>
                </div>

                <form id="create_blog" method="post" action="create_blog.php" enctype="multipart/form-data"
                    class="p-4 md:p-5 space-y-4 bg-gray-800">

                    <!-- input nascosto per la categoria selezionata -->
                    <input type="hidden" id="hidden_category_input" name="category" value="">

                    <!-- title -->
                    <div>
                        <input id="title" type="text" name="title" 
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Title"
                            value = "<?php echo htmlspecialchars($title) ?>" >
                    </div>

                    <!-- category dropdown -->
                    <div>
                        <button id="dropdownDefaultButton" data-dropdown-toggle="dropdown_category"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none
                                    focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center
                                    inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700
                                    dark:focus:ring-blue-800" type="button">
                            <div id="dropdown_category_selected">Select a Category</div>
                            <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" 
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 4 4 4-4" />
                            </svg>
                        </button>

                        <!-- dropdown menu -->
                        <div id="dropdown_category"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg
                                    shadow w-44 dark:bg-gray-700 custom-dropdown">
                            <ul id="select_category" class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton">
                            </ul>
                        </div>
                    </div>

                    <!-- subcategory dropdown -->
                    <div class="mt-4">
                        <button id="dropdownDefaultButtonSub"
                                type="button"
                                class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4
                                    focus:outline-none focus:ring-blue-300 font-medium rounded-lg
                                    text-sm px-5 py-2.5 inline-flex items-center
                                    dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <div id="dropdown_subcategory_selected"> 
                                Select a Subcategory (optional)
                            </div>
                            <svg class="w-2.5 h-2.5 ml-2" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>

                        <!-- subcategory dropdown -->
                        <div id="dropdown_subcategory"
                            class="z-10 hidden bg-white divide-y divide-gray-100
                                    rounded-lg shadow w-44 dark:bg-gray-700 custom-dropdown">
                            <ul id="select_subcategory" class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButtonSub">
                                <!-- popolato nello script -->
                            </ul>
                        </div>
                    </div>

                    <!-- input nascosto per store delle subcategory selezionate -->
                    <input type="hidden" id="hidden_subcategory_input" name="subcategory" value="">


                    <!-- description -->
                    <div>
                        <textarea id="description" name="description" rows="4"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Write a description..."><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <!-- image upload -->
                    <label class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 
                                rounded-lg cursor-pointer inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M.5 1.75A1.25 1.25 0 0 1 1.75.5h16.5A1.25 1.25 0 0 1 19.5 1.75v12.5A1.25 1.25 0 0 1 18.25 15.5H1.75A1.25 1.25 0 0 1 .5 14.25V1.75Zm5.5 6.25 3 3 7-7"/>
                        </svg>
                        <span>Choose Image</span>
                        <input type="file" name="image" accept="image/png,image/jpg,image/jpeg">
                    </label>

                    <!-- submit button -->
                    <div>
                        <button name="create_blog_sub" type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-300">
                            Create
                        </button>
                    </div>

                    <!-- message after submission -->
                    <div id="message" class="mt-4 text-sm text-gray-300"></div>

                </form>
            </div>
        </div>
    
    </div>
</body>

<script src="../js/jquery.js"></script>
<script type="text/javascript">

    $(document).ready(function () {
        $("#create_blog").submit(function (event) {

            let error = '';

            //if title empty
            if ($("#title").val () === "") { 
                error += "Title required.<br>";
                $("#title").addClass('border-red-500').removeClass('border-green-500');
            } else {
                $("#title").addClass('border-green-500').removeClass('border-red-500');
            }

            //ff description empty
            if ($("#description").val() === "") { 
                error += "Description required.<br>";
                $("#description").addClass('border-red-500').removeClass('border-green-500');
            } else {
                $("#description").addClass('border-green-500').removeClass('border-red-500');
            }

            //prevent default form submission
            if (error !== "") {
                event.preventDefault(); 
                $("#error").html(`
                    <div class="bg-red-100 text-red-800 p-4 rounded-lg" role="alert">
                        ${error}
                    </div>
                `);
            }     
        });

        //script to handle PHP errors (applica alert rosso se il div degli errori è non vuoto)
        if ($('#error').children().length > 0) { //se error è non vuoto
            $("#error").addClass("bg-red-100 text-red-700 border border-red-500 p-4 rounded");
        } else {
            $("#error").removeClass("bg-red-100 text-red-700 border border-red-500 p-4 rounded");
        }
    });

</script>



<script>
document.addEventListener('DOMContentLoaded', () => {
    //categories dati da php
    const categories = <?php echo json_encode($categories, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

    //elementi per category
    const dropdownToggleButton = document.getElementById('dropdownDefaultButton');
    const dropdownMenu = document.getElementById('dropdown_category');
    const dropdownCategorySelected = document.getElementById('dropdown_category_selected');
    const selectCategory = document.getElementById('select_category');
    const hiddenCategoryInput = document.getElementById('hidden_category_input');

    const subBtn = document.getElementById('dropdownDefaultButtonSub');
    

    //popula il category dropdown ogni volta che lo apro
    function populateCategories() {
        selectCategory.innerHTML = '';

        //add new category input
        const inputLi = document.createElement('li');
        inputLi.innerHTML = `
            <input id="input_category" type="text"
                   class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600
                          text-white focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Add New Category">
        `;
        selectCategory.appendChild(inputLi);

        //categories esistenti
        categories.forEach(cat => {
            const li = document.createElement('li');
            const a  = document.createElement('a');
            a.href   = '#';
            a.textContent = cat.name;
            a.classList.add(
                'block','px-4','py-2','hover:bg-gray-100',
                'dark:hover:bg-gray-600','dark:hover:text-white'
            );
            a.dataset.categoryId = cat.id;
            li.appendChild(a);
            selectCategory.appendChild(li);
        });

        //premere enter per selezionare la new category
        const inputCategory = document.getElementById('input_category');
        inputCategory.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const newCatName = inputCategory.value.trim();
                if (newCatName) {
                    //mostra categoria scrittoa
                    dropdownCategorySelected.textContent = newCatName;
                    //store del nome della categoria in un field hiddet
                    hiddenCategoryInput.value = newCatName;
                    //pulisce e nasconde
                    inputCategory.value = '';
                    dropdownMenu.classList.add('hidden');
                    //mostra il bottone subcategory (che era nascosto)
                    subBtn.classList.remove('hidden');

                    //evento senza id numerico
                    const categoryChangedEvent = new CustomEvent('categoryChanged', {
                        detail: {
                            categoryId: null,
                            categoryName: newCatName
                        }
                    });
                    document.dispatchEvent(categoryChangedEvent);
                }
            }
        });
    }

    //selezione categoria da una lista di categorie esistenti
    selectCategory.addEventListener('click', (event) => {
        if (event.target.tagName === 'A') {
            event.preventDefault();
            const selectedCatName = event.target.textContent;
            const selectedCatId   = event.target.dataset.categoryId;
            dropdownCategorySelected.textContent = selectedCatName;
            hiddenCategoryInput.value = selectedCatName;
            dropdownMenu.classList.add('hidden');

            //mostra il bottone subcategories
            subBtn.classList.remove('hidden');

            //fire an event with numeric ID
            const categoryChangedEvent = new CustomEvent('categoryChanged', {
                detail: {
                    categoryId: parseInt(selectedCatId,10),
                    categoryName: selectedCatName
                }
            });
            document.dispatchEvent(categoryChangedEvent);
        }
    });

    //toggle the category dropdown
    dropdownToggleButton.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
        if (!dropdownMenu.classList.contains('hidden')) {
            populateCategories();
        }
    });

    //chiuso category dropdown se clikko fuori
    document.addEventListener('click', (event) => {
        if (!dropdownMenu.contains(event.target) 
            && !dropdownToggleButton.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    //elementi subcategory
    const dropdownToggleSub = document.getElementById('dropdownDefaultButtonSub');
    const dropdownSubMenu = document.getElementById('dropdown_subcategory');
    const dropdownSubSelected = document.getElementById('dropdown_subcategory_selected');
    const selectSubcategory = document.getElementById('select_subcategory');
    const hiddenSubcategoryInput = document.getElementById('hidden_subcategory_input');

    //costruzione mappa: dal categoryId => array of subcategories
    const subcategoriesMap = {};
    <?php
    foreach ($categories as $cat) {
        $catId = (int)$cat['id'];
        $resSub = mysqli_query($conn, "SELECT * FROM subcategory WHERE id_category = $catId");
        $subArr = [];
        while ($row = mysqli_fetch_assoc($resSub)) {
            $subArr[] = $row;
        }
        echo "subcategoriesMap[$catId] = " . json_encode($subArr) . ";\n";
    }
    ?>

    //toggle subcategory dropdown
    console.log("Attaching listener to subcategory button...");
    console.log("Sub button element is:", dropdownToggleSub);
    dropdownToggleSub.addEventListener('click', (e) => {
        console.log("Subcategory button clicked");
        dropdownSubMenu.classList.toggle('hidden');
    });

    //chiudo se clikko fuori
    document.addEventListener('click', (event) => {
        if (!dropdownSubMenu.contains(event.target) 
            && !dropdownToggleSub.contains(event.target)) {
            dropdownSubMenu.classList.add('hidden');
        }
    });

    //popula le subcategories dato un ID category
    function populateSubcategories(categoryId) {
        selectSubcategory.innerHTML = '';

        //input per nuova subcategory
        const inputLi = document.createElement('li');
        inputLi.innerHTML = `
            <input id="input_subcategory" type="text"
                   class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600
                          text-white focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Add New Subcategory">
        `;
        selectSubcategory.appendChild(inputLi);

        //subcategories esistenti
        const subList = subcategoriesMap[categoryId] || [];
        subList.forEach(sub => {
            const li = document.createElement('li');
            const a  = document.createElement('a');
            a.href   = '#';
            a.textContent = sub.name;
            a.classList.add(
                'block','px-4','py-2','hover:bg-gray-100',
                'dark:hover:bg-gray-600','dark:hover:text-white'
            );
            a.dataset.subcategoryId = sub.id;
            li.appendChild(a);
            selectSubcategory.appendChild(li);
        });

        //press enter => new sub
        const inputSub = document.getElementById('input_subcategory');
        inputSub.addEventListener('keydown', (evt) => {
            if (evt.key === 'Enter') {
                evt.preventDefault();
                const newSubName = inputSub.value.trim();
                if (newSubName) {
                    dropdownSubSelected.textContent = newSubName;
                    hiddenSubcategoryInput.value     = newSubName;
                    inputSub.value = '';
                    dropdownSubMenu.classList.add('hidden');
                }
            }
        });
    }

    //se user clikka una subcategoria esistente
    selectSubcategory.addEventListener('click', (evt) => {
        if (evt.target.tagName === 'A') {
            evt.preventDefault();
            const selectedSubName = evt.target.textContent;
            dropdownSubSelected.textContent = selectedSubName;
            hiddenSubcategoryInput.value     = selectedSubName;
            dropdownSubMenu.classList.add('hidden');
        }
    });

    //listen for custom "categoryChanged" event  dallo script della category
    document.addEventListener('categoryChanged', (e) => {
        const catId = e.detail.categoryId;
        if (catId) {
            //dato l'id della category, popula la subcategoriesMap
            populateSubcategories(catId);
        } else {
            //if brand-new typed category => no existing subs
            selectSubcategory.innerHTML = `
                <li>
                    <input id="input_subcategory" type="text"
                           class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600
                                  text-white focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Add New Subcategory">
                </li>
            `;
            //type a new subcategory
            const inputSub = document.getElementById('input_subcategory');
            inputSub.addEventListener('keydown', (evt) => {
                if (evt.key === 'Enter') {
                    evt.preventDefault();
                    const newSubName = inputSub.value.trim();
                    if (newSubName) {
                        dropdownSubSelected.textContent = newSubName;
                        hiddenSubcategoryInput.value     = newSubName;
                        inputSub.value = '';
                        dropdownSubMenu.classList.add('hidden');
                    }
                }
            });
        }
    });
});
</script>

</html>
