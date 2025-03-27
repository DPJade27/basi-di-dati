<?php
/*
   profile.php permette a un utente autenticato di visualizzare le proprie informazioni personali e di gestire il proprio profilo

    . ontrolla se l'utente è autenticato tramite sessione.
    . recupera i dati personali dell'utente dal database, inclusi:
       - username
       - name
       - surname
       - DOB
       - email
       - tipo di account (premium o regular)
    . due opzioni principali:
       - modifica dei dati personali tramite un link a "edit_profile.php".
       - eliminazione dell'account tramite un link a "delete_profile.php".
*/


//connessione al db
include('db_connect.php');
include('header.php'); 

$dati_user = $ris_user = $ris_blog = $username = '';
$num_blog = 0;
$is_premium = false;

if (isset($_SESSION['username'])) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);

    $sqlUser = "SELECT * FROM user WHERE username = '$username'";
    $ris_user = mysqli_query($conn, $sqlUser);
    if ($ris_user && mysqli_num_rows($ris_user) > 0) {
        $dati_user = mysqli_fetch_assoc($ris_user);
    } else {
        die("Error fetching user data: " . mysqli_error($conn));
    }

    //check se user è premium
    $is_premium = $dati_user['is_premium'] ?? false;

    //chiude connessione
    mysqli_close($conn);
} else {
    die("Unauthorized access: Please log in.");
}

?>


<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
    
<body class="bg-black text-white">
    

    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- header -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 p-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-white">My Profile</h1>
                <i class="fas fa-user-circle text-white text-4xl"></i>
            </div>
        </div>

        <!-- profile content -->
        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- left side: info profilo -->
                <div class="space-y-6">
                    <!-- username -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-600">Username</h2>
                        <p class="text-xl text-gray-900"><?php echo htmlspecialchars($dati_user['username'] ?? ''); ?>
                        </p>
                    </div>
                    <!-- name -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-600">Name</h2>
                        <p class="text-xl text-gray-900"><?php echo htmlspecialchars($dati_user['name'] ?? 'N/A'); ?></p>
                    </div>
                    <!-- surname -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-600">Surname</h2>
                        <p class="text-xl text-gray-900"><?php echo htmlspecialchars($dati_user['surname'] ?? 'N/A'); ?></p>
                    </div>
                    <!-- DOB -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-600">Date of Birth</h2>
                        <p class="text-xl text-gray-900"><?php echo htmlspecialchars($dati_user['date_of_birth'] ?? 'N/A'); ?></p>
                    </div>
                    <!-- email -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-600">Email</h2>
                        <p class="text-xl text-gray-900"><?php echo htmlspecialchars($dati_user['email'] ?? 'N/A'); ?></p>
                    </div>
                    <!-- tipo user -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-600">Account Type</h2>
                        <p class="text-xl text-gray-900">
                            <?php echo isset($dati_user['is_premium']) && $dati_user['is_premium'] ? 'Premium User' : 'Regular User'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-8 space-x-4">
                <!-- edit profile left -->
                <a href="edit_profile.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 focus:outline-none transition duration-200">
                    Edit Personal Information
                </a>
                
                <!-- delete profile right -->
                <a href="delete_profile.php" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 focus:outline-none transition duration-200">
                    Delete Profile
                </a>
            </div>
        </div>
    </div>


</body>
</html>