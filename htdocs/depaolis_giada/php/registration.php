<?php 

/*
registration.php è la pagina responsabile per gestire la registrazione di un user
funzionalità:
- validazione dei campi: controllo dei dati inseriti dall'utente (username, name, surname, email, DOB, password)
- salvataggio nel database: inserimento delle informazioni degli utenti nel database.
- gestione errori: messaggi di errore in caso di input non valido o problemi durante la registrazione.
- interfaccia utente con Tailwind CSS
- reindirizzamento post-registrazione: dopo una registrazione corretta, l'utente viene reindirizzato alla pagina di login.
*/

include('server.php'); ?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white flex justify-center items-center min-h-screen">

    <div class="container mx-auto px-4 py-8">
        
        <!-- REGISTRATION FORM WITH TAILWIND -->
        <div class="container mx-auto py-10">
        <div class="max-w-lg mx-auto bg-gray-800 p-6 rounded-lg shadow-lg">
        <h4 class="text-2xl font-semibold text-center text-white">Registration</h4>

        <!-- mex di errore -->
        <div id="errore" class="text-red-500 my-4">
            <?php include('errors.php'); ?>
        </div>

        <form id="reg_user" method="post" action="registration.php" class="space-y-6">
            
            <!-- username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-200"></label>
                <input id="username"
                       type="text"
                       name="username"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Username:"
                       value="<?php echo $username; ?>">
            </div>

            <!-- name -->
            <div>
                <label for="input-name" class="block text-sm font-medium text-gray-200"></label>
                <input id="input-name"
                       type="text"
                       name="name"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Name:"
                       value="<?php echo $name; ?>">
            </div>

            <!-- surname -->
            <div>
                <label for="input-surname" class="block text-sm font-medium text-gray-200"></label>
                <input id="input-surname"
                       type="text"
                       name="surname"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Surname:"
                       value="<?php echo $surname; ?>">
            </div>

            <!-- email -->
            <div>
                <label for="input-email" class="block text-sm font-medium text-gray-200"></label>
                <input id="input-email"
                       type="email"
                       name="email"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Email:"
                       value="<?php echo $email; ?>">
            </div>


            <!-- date of birth con calendario -->
            <div>
                <label for="input-date_of_birth" class="block text-sm font-medium text-gray-200"><strong>Date of Birth:</strong></label>
                <input id="input-date_of_birth"
                    type="date"
                    name="date_of_birth"
                    class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Date of Birth:"
                    value="<?php $date_of_birth; ?>">

            </div>


            <!-- password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200"></label>
                <input id="password"
                       type="password"
                       name="password"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Password:"
                       value="<?php echo $password; ?>">
                <small id="pwHelp" class="text-gray-400">Password must be at least 8 characters long.</small>
            </div>

            <!-- confirm password -->
            <div>
                <label for="password_confirm" class="block text-sm font-medium text-gray-200"></label>
                <input id="password_confirm"
                       type="password"
                       name="password_confirm"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Confirm Password:">
            </div>

            <!-- submit button -->
            <div class="flex justify-center">
                <button type="submit"
                        class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 rounded-lg text-white font-medium focus:outline-none" name="reg_user">
                    Register
                </button>
            </div>

            <!-- reindirizzamento alla pagina di login se abbiamo già un account -->
            <div class="text-blue-400 text-center w-full mt-4">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </form>

        
        <!-- include jQuery da file locale -->
        <script src="../js/jquery.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                $("form").submit(function (event) {
                    let errore = "";

                //validazione username
                if ($("#username").val() === "") { //se è vuoto
                    errore += "Username is required.<br>";
                    $("#username").addClass('border-red-500').removeClass('border-green-500');
                } else {
                    $("#username").addClass('border-green-500').removeClass('border-red-500');
                }

                //validazione email
                if ($("#input-email").val() === "") { 
                    errore += "Email is required.<br>";
                    $("#input-email").addClass('border-red-500').removeClass('border-green-500');
                } else {
                    $("#input-email").addClass('border-green-500').removeClass('border-red-500');
                }

                //password
                if ($("#password").val() === "" || $("#password_confirm").val() === "") { //se è vuoto
                    errore += "Password is required.<br>";
                    $("#password").addClass('border-red-500').removeClass('border-green-500');
                } else if ($("#password").val().length < 8) {
                    errore += "The password must be at least 8 characters long.<br>"; //se è più piccola di 8 caratteri
                    $("#password").addClass('border-red-500').removeClass('border-green-500');
                } else {
                    $("#password").addClass('border-green-500').removeClass('border-red-500');
                }

                //passowrd match
                if ($("#password").val() !== $("#password_confirm").val()) {
                    errore += "Passwords do not match.<br>";
                    $("#password").addClass('border-red-500');
                    $("#password_confirm").addClass('border-red-500');
                } else {
                    $("#password_confirm").removeClass('border-red-500').addClass('border-green-500');
                }

                //mostra errori se ci sono
                if (errore !== "") {
                    /*senza event.preventDefault() il form verrebbe inviato anche se l'input è invalido e invierebbe dati errati al server
                    garantisce la gestione degli errori e da feedback all'utente prima dell'invio.*/
                    event.preventDefault();  
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