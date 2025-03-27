<?php
/*gestisce le azioni “mi piace” e “non mi piace” per i post in un'applicazione blog
* convalida che l'utente sia connesso e recupera il suo ID utente
* legge l'ID del post (`idPost`) e l'azione (`like` o `unlike`) dalla richiesta POST
    - se l'azione è `like`, inserisce una riga nella tabella `like_`, associando il post all'utente 
    - se nel post c'è già like (chiave duplicata), ignora la richiesta
    - se `unlike`, rimuove la riga corrispondente
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

//legge dati richiesti
$idPost = $_POST['idPost'] ?? '';
$action = $_POST['action'] ?? '';

$idPost = mysqli_real_escape_string($conn, $idPost);

if ($action === 'like') {
    //inserisce riga
    $sqlLike = " INSERT INTO like_ (id_post, id_user)
                VALUES ('$idPost', '$id_user')
                ON DUPLICATE KEY UPDATE id_post=id_post";
    if (mysqli_query($conn, $sqlLike)) {
        echo 'success-like';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
} elseif ($action === 'unlike') { //rimuove riga
    $sqlUnlike = "DELETE FROM like_ 
                WHERE id_post = '$idPost'
                AND id_user = '$id_user'";
    if (mysqli_query($conn, $sqlUnlike)) {
        echo 'success-unlike';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
} else {
    echo 'Invalid action';
}

?>