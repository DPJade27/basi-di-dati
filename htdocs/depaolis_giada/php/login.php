<?php 

/*
login.php è la pagina responsabile per gestire la login di un user
funzionalità:
- validazione dei campi: controllo dei dati inseriti dall'utente (username, password)
- autenticazione del database: verifica le credenziali con il db
- gestione errori: messaggi di errore in caso di input non valido o problemi durante la login
- interfaccia utente con Tailwind CSS
- reindirizzamento post-login: dopo una login corretta, l'utente viene reindirizzato alla pagina principale (index.php che si chiama Feed)
*/

include('server.php'); 

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white flex justify-center items-center min-h-screen">

    <div class="container mx-auto px-4 py-8">

        <!-- messaggio di registrazione success -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6" role="alert">
                <strong><?php echo $_SESSION['success']; ?></strong>
            </div>
            <?php unset($_SESSION['success']); //clear il messaggio success ?>
        <?php endif; ?>

        
        <!-- LOGIN FORM WITH TAILWIND -->
        <div class="container mx-auto py-10">
        <div class="max-w-lg mx-auto bg-gray-800 p-6 rounded-lg shadow-lg">
        <h4 class="text-2xl font-semibold text-center text-white">Log In</h4>

        <!-- error messages -->
        <div id="accessoF">
                <?php echo $accessoF; ?>
        </div>

        <!-- errors -->
        <div id="errore" class="text-red-500 my-4">
            <?php include('errors.php'); ?>
        </div>

        <form id="formLog" method="post" action="login.php" class="space-y-6">
            
            <!-- username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-200"></label>
                <input id="username"
                       type="text"
                       name="username"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Username:"
                       value="<?php echo htmlspecialchars($username); ?>">
            </div>

            <!-- password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200"></label>
                <input id="password"
                       type="password"
                       name="password"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Password:"
                       value="<?php echo htmlspecialchars($password); ?>">
            </div>

            <!-- submit button -->
            <div class="flex justify-center">
                <button type="submit-login"
                        class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 rounded-lg text-white font-medium focus:outline-none" name="login-user">
                    Log In
                </button>
            </div>

            <!-- se non hai ancora un account registrati -->
            <div class="text-blue-400 text-center w-full mt-4">
                Don't have an account? <a href="registration.php">Register</a>
            </div>
        </form>

        <!--  jQuery from a local file -->
        <script src="../js/jquery.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                $("form").submit(function (event) {

                    //nasconde l'avviso errore query quando reinserisco nuovi dati
                    $('#accessoF').hide();
                    let errore = "";

                    // check username
                    if ($("#username").val() === "") {
                        errore += "Username required.<br>";
                        $("#username").addClass('border-red-500').removeClass('border-green-500');
                    } else {
                        $("#username").addClass('border-green-500').removeClass('border-red-500');
                    }

                    // check password
                    if ($("#password").val() === "") {
                        errore += "Password required.<br>";
                        $("#password").addClass('border-red-500').removeClass('border-green-500');
                    } else {
                        $("#password").addClass('border-green-500').removeClass('border-red-500');
                    }

                    // se errors exist, previene form submission and mostra messaggio errore
                    if (errore !== "") {
                        event.preventDefault(); // prevenzione submit di default
                        $("#errore").html(`
                            <div class="bg-red-100 text-red-800 p-4 rounded-lg" role="alert">
                                ${errore}
                            </div>
                        `);
                    }
                });
            });

        </script>

        
</body>

</html>
                            
