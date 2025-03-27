<?php
/**
 * delete_profile consente agli utenti loggati di eliminare il proprio profilo e tutti i dati associati dal database
 * verifica che l'utente sia loggato tramite la sessione
 * se l'utente conferma l'eliminazione tramite il modulo:
 *    - elimina tutti i commenti dell'utente
 *    - elimina tutti i like dell'utente
 *    - rimuove l'utente come coautore da eventuali blog
 *    - elimina tutti i post creati dall'utente
 *    - elimina tutti i blog creati dall'utente
 *    - elimina il profilo dell'utente dalla tabella `user`
 * utilizza una transazione per garantire che tutte le eliminazioni vengano eseguite correttamente
 * dopo l'eliminazione, distrugge la sessione dell'utente e lo reindirizza alla homepage con un messaggio di conferma
 * gestisce eventuali errori di database e fornisce un'opzione per annullare l'operazione
 */


session_start();
include('db_connect.php');

//check user
if (!isset($_SESSION['username'])) {
    die("Unauthorized access. Please <a href='login.php'>log in</a> to delete your profile.");
}

$username = mysqli_real_escape_string($conn, $_SESSION['username']);

//handler della delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //elimina i dati relativi all'user
    $sqlDeleteComments = "DELETE FROM comment WHERE id_user = (SELECT id FROM user WHERE username = '$username')";
    $sqlDeleteLikes = "DELETE FROM like_ WHERE id_user = (SELECT id FROM user WHERE username = '$username')";
    $sqlDeleteCoauthorships = "DELETE FROM blog_coauthor WHERE id_user = (SELECT id FROM user WHERE username = '$username')";
    $sqlDeletePosts = "DELETE FROM post_ WHERE id_user = (SELECT id FROM user WHERE username = '$username')";
    $sqlDeleteBlogs = "DELETE FROM blog_ WHERE id_user = (SELECT id FROM user WHERE username = '$username')";
    $sqlDeleteUser = "DELETE FROM user WHERE username = '$username'";

    //transazione query
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, $sqlDeleteComments);
        mysqli_query($conn, $sqlDeleteLikes);
        mysqli_query($conn, $sqlDeleteCoauthorships);
        mysqli_query($conn, $sqlDeletePosts);
        mysqli_query($conn, $sqlDeleteBlogs);
        mysqli_query($conn, $sqlDeleteUser);

        mysqli_commit($conn);

        //va alla pagina feed
        session_destroy();
        header("Location: index.php?message=Profile deleted successfully.");
        exit();
    } catch (Exception $e) { //cattura eventuali eccezioni che si verificano durante l'esecuzione del blocco try.
        mysqli_rollback($conn); 
        die("Error deleting profile: " . mysqli_error($conn));
    }
}
//con le transazioni è possibile che alcune operazioni nel blocco try non vadano a buon fine
//comando mysqli_rollback annulla tutte le modifiche effettuate al database durante la transazione
//riportando il database allo stato precedente all'inizio della transazione, garantisce l'integrità dei dati, evitando modifiche parziali o incoerenti
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white">

<div class="max-w-md mx-auto bg-white shadow-lg rounded-lg overflow-hidden mt-10">
    <div class="p-6 space-y-4">
        <<h2 class="text-2xl font-semibold text-black text-center">Delete Your Profile</h2>
        <p class="text-gray-800 text-center">
            Are you sure you want to delete your profile? This action cannot be undone, and all your data will be permanently removed.
        </p>

        <!-- conferma eliminazione -->
        <form method="post" action="delete_profile.php" class="space-y-4">
            <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition duration-300">
                Confirm Delete
            </button>
        </form>

        <!-- button cancel -->
        <a href="index.php"
           class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 rounded-lg transition duration-300">
            Cancel
        </a>
    </div>
</div>

</body>
</html>
