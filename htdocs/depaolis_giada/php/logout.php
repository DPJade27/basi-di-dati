<?php

    //import logouts
    include('nav_unauth.php');

    session_start();
    unset($_SESSION['username']);
    session_destroy();
?>

<body>

<div class="min-h-screen flex flex-col justify-center items-center bg-gray-900 text-white">
    <div class="text-center">
        <h1 class="text-3xl font-semibold mb-4">Disconnected</h1>

        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-400 text-gray-300 bg-gray-800 rounded-lg hover:bg-gray-700 hover:border-gray-300 hover:text-white transition">
            <i class="fa fa-home mr-2"></i> Torna alla Home
        </a>
        
    </div>
</div>

</body>
