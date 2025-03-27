<?php
/**
 * delete_comment consente agli utenti loggati di eliminare un commento
 * verifica se l'utente è loggato tramite la sessione
 * recupera l'ID dell'utente loggato dal database usando il suo username
 * valida l'ID del commento ricevuto tramite query string
 * check se il commento esiste e recupera l'ID dell'autore del commento e l'ID del blog associato
 * confronta l'ID dell'autore del commento con l'ID dell'utente loggato. Solo l'autore può eliminare il proprio commento
 * se autorizzato, elimina il commento dal database
 * dopo la cancellazione, reindirizza l'utente alla pagina del post del blog corrispondente
 * gestisce eventuali errori come utente non loggato, commento inesistente o mancanza di autorizzazione
 */

session_start();
include('db_connect.php');

//user loggato
if (!isset($_SESSION['username'])) {
    die('You must be logged in to delete a comment.');
}

$username = $_SESSION['username'];

//fetch the user ID
$sqlUser = "SELECT id FROM user WHERE username = '$username'";
$resUser = mysqli_query($conn, $sqlUser);
if (!$resUser || mysqli_num_rows($resUser) === 0) {
    die('Error: User not found.');
}
$id_user = mysqli_fetch_assoc($resUser)['id'];

//validazione comment ID
if (!isset($_GET['idComment']) || empty($_GET['idComment'])) {
    die('Missing comment ID.');
}
$idComment = mysqli_real_escape_string($conn, $_GET['idComment']);

//check se il comment esiste e fetch comment author + blog ID
$sqlCheck = "SELECT c.id_user, p.id_blog
            FROM comment c
            JOIN post_ p ON c.id_post = p.id
            WHERE c.id = '$idComment'";
$resCheck = mysqli_query($conn, $sqlCheck);

if (!$resCheck || mysqli_num_rows($resCheck) === 0) {
    die('Comment not found.');
}
$rowComment = mysqli_fetch_assoc($resCheck);

//se logged-in user non è author, block
if ((int)$rowComment['id_user'] !== (int)$id_user) {
    die('Unauthorized. You can only delete your own comments.');
}

//se autorizzato, elimina commento dal db
$sqlDelete = "DELETE FROM comment WHERE id = '$idComment'";
if (mysqli_query($conn, $sqlDelete)) {
    //redirect a post.php
    $blogId = $rowComment['id_blog'];
    header("Location: post.php?id=$blogId");
    exit();
} else {
    die('Error deleting comment: ' . mysqli_error($conn));
}

mysqli_close($conn);
?>
