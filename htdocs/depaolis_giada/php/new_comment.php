<?php
/*
    new_comment gestisce l'aggiunta di commenti ai post 
    . verifica che l'utente sia loggato e recupera il suo ID utente
    . legge l'ID del post (`idPost`) e il testo del commento (`comment`) dalla richiesta POST
       - Si assicura che siano forniti sia l'ID del post che il testo del commento
    . sanitizza i dati di input per prevenire SQL injection
    . Inserisce il commento nella tabella `comment` con:
       - testo del commento fornito
       - ID dell'utente loggato
       - ID del post associato
       - timestamp corrente (`created`)
    . restituisce "success" in caso di inserimento avvenuto con successo, o un messaggio di errore se la query fallisce
*/

session_start();
include('db_connect.php');

if (!isset($_SESSION['username'])) {
    echo 'Not logged in';
    exit;
}

$username = $_SESSION['username'];
$sqlUser = "SELECT id FROM user WHERE username = '$username'";
$resUser = mysqli_query($conn, $sqlUser);
if (!$resUser || mysqli_num_rows($resUser) === 0) {
    echo 'User not found';
    exit;
}
$id_user = mysqli_fetch_assoc($resUser)['id'];

//estrazione dati
$idPost  = $_POST['idPost'] ?? '';
$comment = trim($_POST['comment'] ?? '');
if (!$idPost || !$comment) {
    echo 'Missing post ID or comment text';
    exit;
}

//inserisce commento
$idPost   = mysqli_real_escape_string($conn, $idPost);
$comment  = mysqli_real_escape_string($conn, $comment);
$created  = date('Y-m-d H:i:s');

$sqlInsertComment = "INSERT INTO comment (text, created, id_user, id_post)
                    VALUES ('$comment', '$created', '$id_user', '$idPost')";
if (mysqli_query($conn, $sqlInsertComment)) {
    echo 'success';
} else {
    echo 'Error: ' . mysqli_error($conn);
}
