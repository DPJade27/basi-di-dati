<?php
/*
    premium.php consente agli utenti registrati di abbonarsi
    . controlla che l'utente sia autenticato
    . mostra un modulo di pagamento con convalida dei dati:
       - nome del titolare della carta
       - numero della carta di credito
       - data di scadenza
       - CVV 
    . la tabella premium non esiste, is_premium è una colonna della tabella user, quindi i dati inseriti per il pagamento sono finti
      non vengono realmente inseriti nel database, ma se nel form tutti i dati sono inseriti e non ci sono errori is_premium==true
    . una volta Premium, l'utente può creare un numero illimitato di blog
*/

//include database connection and session start
include('db_connect.php');
include('header.php');

if (!isset($_SESSION)) {
    session_start();
}

//si assicura che user è loggato
if (!isset($_SESSION['username'])) {
    die("Unauthorized access: Please log in.");
}

$username = $_SESSION['username'];

//recupera status premium
$sqlIsPremium = "SELECT is_premium FROM user WHERE username = '$username'";
$isPremiumResult = mysqli_query($conn, $sqlIsPremium);
$is_premium = mysqli_fetch_assoc($isPremiumResult)['is_premium'] ?? false;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //validazione input
    $fullName = $_POST['name'] ?? '';
    $cardNumber = $_POST['card_number'] ?? '';
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    if (empty($fullName)) {
        $errors[] = "Cardholder name is required.";
    }
    if (empty($cardNumber)) {
        $errors[] = "The are no card number.";
    }
    if (empty($expiry) || !preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
        $errors[] = "Expiration date must be in MM/YY format.";
    }
    if (empty($cvv) || !preg_match('/^\d{3}$/', $cvv)) {
        $errors[] = "CVV must be 3 digits.";
    }

    //se no errorse non vuoto, update is_premium è true
    if (empty($errors)) {
        $sqlUpdatePremium = "UPDATE user SET is_premium = 1 WHERE username = '$username'";
        if (mysqli_query($conn, $sqlUpdatePremium)) {
            $is_premium = true;
        } else {
            $errors[] = "Failed to update premium status: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 shadow-lg rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-white mb-6">Go Premium</h1>
        <p class="text-center text-gray-400 mb-4">You can create all the blogs you want!</p>

        <?php if ($is_premium): ?>
            <div class="bg-green-500 text-white p-4 rounded">
                <p>You are now a Premium User!</p>
                <p>Go to <a href="myblogs.php" class="underline hover:text-gray-200">My Blogs</a> to explore and manage your content!</p>
            </div>
        <?php else: ?>

        <!-- error mex -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- form pagamento -->
        <form id="payment-form" action="premium.php" method="POST" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-200 mb-2">Cardholder Name</label>
                <input type="text" id="name" name="name" placeholder="Full Name" class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="card-number" class="block text-sm font-medium text-gray-200 mb-2">Card Number</label>
                <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="flex space-x-4">
                <div class="flex-1">
                    <label for="expiry" class="block text-sm font-medium text-gray-200 mb-2">Expiration Date</label>
                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY" class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="flex-1">
                    <label for="cvv" class="block text-sm font-medium text-gray-200 mb-2">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Subscribe Now
            </button>
        </form>
        <?php endif; ?>
    </div>
</body>

</html>
