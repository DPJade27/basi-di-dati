<?php
/**
 * delete_post consente di eliminare un post da un blog, gestendo l'autorizzazione in base al ruolo dell'utente
 * verifica se l'utente è loggato tramite la sessione
 * valida l'ID del post ricevuto tramite query string
 * recupera l'ID dell'utente loggato e verifica se è l'autore del post, il proprietario del blog o un coautore
 *    - il proprietario del blog può eliminare qualsiasi post
 *    - i coautori possono eliminare solo i post da loro creati
 * se autorizzato, elimina il post dalla tabella `post_`.
 *    - elimino anche le immagini associate nella tabella post_image
 * reindirizza l'utente alla pagina del blog a cui il post appartiene
 * gestisce errori come utente non loggato, post inesistente o mancanza di autorizzazione.
 */

ob_start();
include('db_connect.php');
include('header.php');

//utente loggato
if (!isset($_SESSION['username'])) {
    die('Unauthorized. Please log in.');
}
$username = $_SESSION['username'];

//post ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Missing post ID');
}
$post_id = (int)$_GET['id'];

//user corrente
$sqlUser = "SELECT id FROM user WHERE username = '$username'";
$resUser = mysqli_query($conn, $sqlUser);
if (!$resUser || mysqli_num_rows($resUser) === 0) {
    die("User not found.");
}
$id_user = mysqli_fetch_assoc($resUser)['id'];

//prende post, chi ha creato il post e a quale blog appartiene
$sqlPost = "SELECT p.id, p.id_user, p.id_blog, b.id_user AS blog_owner
            FROM post_ p
            JOIN blog_ b ON p.id_blog = b.id
            WHERE p.id = '$post_id'";
$resPost = mysqli_query($conn, $sqlPost);
if (!$resPost || mysqli_num_rows($resPost) === 0) {
    die('Post not found.');
}
$row = mysqli_fetch_assoc($resPost);
$postCreator = $row['id_user'];
$blogOwner   = $row['blog_owner'];
$blogId      = $row['id_blog'];

//check se è il coauthore
$sqlCoauthor = "SELECT id_user FROM blog_coauthor WHERE id_blog = '$blogId'";
$resCoauthor = mysqli_query($conn, $sqlCoauthor);
$coauthors = [];
if ($resCoauthor && mysqli_num_rows($resCoauthor) > 0) {
    while ($c = mysqli_fetch_assoc($resCoauthor)) {
        $coauthors[] = $c['id_user'];
    }
}
$isCoauthor  = in_array($id_user, $coauthors);
$isOwner     = ($id_user == $blogOwner);

//se proprietario può eliminare ogni post
//se coauthor solo i post da lui creati
if ($isOwner || ($isCoauthor && $postCreator == $id_user)) {
    //elimina dal db
    $sqlDel = "DELETE FROM post_ WHERE id = '$post_id'";
    if (mysqli_query($conn, $sqlDel)) {
        header("Location: post.php?id=$blogId");
        exit();
    } else {
        die('Error deleting post: ' . mysqli_error($conn));
    }
} else {
    die('Unauthorized to delete this post.');
}

ob_end_flush();
