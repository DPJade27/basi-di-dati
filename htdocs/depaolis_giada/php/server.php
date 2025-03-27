<?php

/*
server.php è la pagina responsabile per gestire l'autenticazinoe di un user e la registrazione
- registration: valida e salva le info di un nuovo user nel database
- login: autenticazione di un user e inizio della sessione 
- errori: gestione degli errori durante le fasi di registrazione e login --> se una password e un username sono già in uso da errore
*/

session_start(); //inizio sessione

//inizializzazione variabili
$username = $name = $surname = $email = $date_of_birth = $password = $password_confirm = '';
//array vuoto per errori
$errors = array();
$accessoF = '';

//connessione al db
include('db_connect.php');

//REGISTRAZIONE UTENTE
if (isset($_POST['reg_user'])) {

    //valori del db
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    //validazione form --> array_push() append error
    if (empty($username)) {  //un username non può essere vuoto
        array_push($errors, "An username is required");
    }
    if (empty($password)) {  //una pw non può essere vuota 
        array_push($errors, "A password is required");
    } 

    //check db username già esistente
    $user_check_query = "SELECT * FROM user WHERE username = '$username' OR email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['username'] === $username) {
            array_push($errors, "Username already in use");
        }

        if ($user['email'] === $email) {
            array_push($errors, "Email already existing");
        }
    }

    //if no errors, register
    if (count($errors) == 0) {
        $password = md5($password); //encrypt the pw before saving into db

        $query = "INSERT INTO user(username, name, surname, email, date_of_birth, password) VALUES ('$username', '$name', '$surname', '$email', '$date_of_birth', '$password')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "User registered successfully! :)"; //store messagge success
            header('Location: login.php'); //redirect to login page
            exit(); //lo script stops after the redirect
        } else {
            array_push($errors, "Error during registration. Please try again.");
        }
    }
}


//LOGIN UTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //recupera in modo sicuro username e password, garantendo che non ci siano chiavi di array non definite    
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : '';

    //validazione input
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    //procede solo se non ci sono errori di valutazione
    if (count($errors) === 0) {
        $password = md5($password); //encrypt password

        $query = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 1) {
            //successful login
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit(); //stop
        } else {
            //username or password incorretti
            $accessoF = '<div class="bg-red-100 text-red-800 p-4 rounded-lg" role="alert"><p>Username or Password is incorrect</p></div>';
        }
    }
}

?>