<?php
/*
 * edit_profile consente agli utenti loggati di modificare il proprio profilo
 * - recupera i dati dell'utente loggato dal database e li precompila in un modulo
 * - permette all'utente di aggiornare informazioni come nome, cognome, data di nascita e email
 * - aggiorna i dati nel database quando il modulo viene inviato
 * - mostra eventuali errori durante l'aggiornamento
 * 
 * - l'utente deve essere loggato per accedere a questa pagina
 * - dopo un aggiornamento riuscito, l'utente viene reindirizzato alla pagina del profilo.
 */

ob_start();

include('db_connect.php');
include('header.php');


$username = $_SESSION['username'];
$userData = [];

//fetch dati user
$sqlFetchUser = "SELECT * FROM user WHERE username = '$username'";
$result = mysqli_query($conn, $sqlFetchUser);

if ($result && mysqli_num_rows($result) > 0) {
    $userData = mysqli_fetch_assoc($result);
} else {
    die("Error fetching user data: " . mysqli_error($conn));
}

//update dati user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? $userData['name']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname'] ?? $userData['surname']);
    $dateOfBirth = mysqli_real_escape_string($conn, $_POST['date_of_birth'] ?? $userData['date_of_birth']);
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? $userData['email']);

    if (empty($error)) {
        $sqlUpdateUser = "UPDATE user SET 
                          name = '$name', 
                            surname = '$surname', 
                            date_of_birth = '$dateOfBirth', 
                            email = '$email'
                          WHERE username = '$username'";

        if (mysqli_query($conn, $sqlUpdateUser)) {
            header("Location: profile.php");
            exit();
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);

ob_end_flush();
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white">

<div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
    <!-- header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 p-6">
        <h1 class="text-2xl font-bold text-white">Edit Profile</h1>
    </div>

    <!-- edit profile form -->
    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
        <!-- name -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" 
                class="w-full p-3 rounded-lg bg-gray-700 text-white focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- surname -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Surname</label>
            <input type="text" name="surname" value="<?php echo htmlspecialchars($userData['surname']); ?>" 
                class="w-full p-3 rounded-lg bg-gray-700 text-white focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- DOB -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($userData['date_of_birth']); ?>" 
                class="w-full p-3 rounded-lg bg-gray-700 text-white focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" 
                class="w-full p-3 rounded-lg bg-gray-700 text-white focus:ring-blue-500 focus:border-blue-500">
        </div>


        <!-- errors -->
        <?php if (!empty($error)): ?>
            <div class="text-red-500">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- submit button -->
        <div>
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-300">
                Save Changes
            </button>
        </div>
    </form>
</div>

</body>
</html>
