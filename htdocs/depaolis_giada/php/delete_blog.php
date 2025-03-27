<?php
ob_start();
    /*
    * Questa pagina consente agli utenti loggati di eliminare un blog esistente
    * - verifica se l'utente è loggato e ottiene il suo username dalla sessione
    * - recupera l'ID del proprietario del blog tramite una query sul database
    * - confronta l'ID del proprietario con l'ID dell'utente loggato
    * - se l'utente è il proprietario del blog, elimina il blog dal database
    * - altrimenti reindirizza l'utente alla pagina "myblogs.php"
    * - se l'utente non è autorizzato o il blog non esiste, restituisce un messaggio di errore
     */

//connessione al db
include('db_connect.php');
include('header.php');

$username = $_SESSION['username'];
$blogId = $_GET['id'];

//fetch blog owner's username
$sqlFetchOwner = "SELECT id_user FROM blog_ WHERE id = '$blogId'";
$result = mysqli_query($conn, $sqlFetchOwner);

if ($result && mysqli_num_rows($result) > 0) {
    $ownerData = mysqli_fetch_assoc($result);
    $ownerId = $ownerData['id_user'];

    //fetch ID user loggato
    $sqlFetchUserId = "SELECT id FROM user WHERE username = '$username'";
    $userResult = mysqli_query($conn, $sqlFetchUserId);

    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $userData = mysqli_fetch_assoc($userResult);
        $userId = $userData['id'];

        //check ownership
        if ($ownerId == $userId) {
            //delete blog
            $sqlDeleteBlog = "DELETE FROM blog_ WHERE id = $blogId";
            if (mysqli_query($conn, $sqlDeleteBlog)) {
                header('Location: myblogs.php'); //redirect to myblogs
                exit();
            } else {
                die('Error deleting blog: ' . mysqli_error($conn));
            }
        } else {
            die('Unauthorized access: You do not own this blog.');
        }
    } else {
        die('Error fetching user information.');
    }
} else {
    die('Blog not found.');
}

mysqli_close($conn);

 ob_end_flush();
?>
