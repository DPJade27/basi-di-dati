<?php

$username = $_SESSION['username'];

//fetch `is_premium` status
$sqlIsPremium = "SELECT is_premium FROM user WHERE username = '$username'";
$result = mysqli_query($conn, $sqlIsPremium);
$is_premium = false;

if ($result && mysqli_num_rows($result) > 0) {
    $is_premium = (bool)mysqli_fetch_assoc($result)['is_premium'];
}

?>


<html>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        input[type="file"] {
            display: none;
        }

        .custom-dropdown {
            position: absolute;
        }
    </style>

</head>

<body class="bg-black text-white">

    <nav class="bg-gray dark:bg-gray-900 fixed w-full z-20 top-0 start-0 border-b border-gray-200 dark:border-gray-600">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <a href="index.php" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="../img/logo.png" class="h-8 w-8 rounded-full" alt="Logo">
                <span class="text-2xl font-semibold dark:text-white">Sto Blog</span>
            </a>

            <div class="flex md:order-2 items-center space-x-3 rtl:space-x-reverse">
                <div>
                   <!-- button premium --> 
                   <?php if (!$is_premium): ?>
                        <button type="button" onclick="window.location.href='premium.php'"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-4 py-2 font-medium focus:ring-4 focus:ring-yellow-300 dark:focus:ring-yellow-800">
                            Go Premium
                        </button>
                    <?php endif; ?>

                    <!-- button logout --> 
                    <button type="button" onclick="window.location.href='logout.php'"
                            class="bg-gray-700 hover:bg-blue-800 text-white rounded-lg px-4 py-2 font-medium focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Logout
                    </button>
                </div>
            
            <!-- mobile menu toggle button-->
            <button data-collapse-toggle="navbar-sticky" type="button" data-auth="true"
                    class="inline-flex items-center p-2 w-10 h-10 text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="navbar-sticky" aria-expanded="false" aria-label="Toggle navigation">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 17 14" xmlns="http://www.w3.org/2000/svg">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 1h15M1 7h15M1 13h15" />
                    </svg>
            </button>
        </div>

        <!--collapsible menu-->
        <div class="hidden w-full md:flex md:items-center md:justify-between md:w-auto md:order-1"
                id="navbar-sticky">
                <ul class="flex flex-col md:flex-row p-4 md:p-0 font-medium border border-gray-100 rounded-lg bg-gray-50 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 md:space-x-8 rtl:space-x-reverse">
                    <li><a href="index.php" class="py-2 px-3 text-white-700 rounded md:p-0 hover:text-gray-400 dark:text-white dark:hover:text-gray-400">Feed</a></li>
                    <li><a href="profile.php" class="py-2 px-3 text-white-700 rounded md:p-0 hover:text-gray-400 dark:text-white dark:hover:text-gray-400">My Profile</a></li>
                    <li><a href="myblogs.php" class="block py-2 px-3 text-white rounded md:p-0 hover:text-gray-400 dark:text-white dark:hover:text-gray-400">My Blogs</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="h-[6rem]"></div>

</body>

<script type="text/javascript">

document.addEventListener("DOMContentLoaded", () => {
    const toggleButton = document.querySelector("[data-collapse-toggle='navbar-sticky']");
    const collapsibleMenu = document.getElementById("navbar-sticky");

    if (toggleButton && collapsibleMenu) {
        toggleButton.addEventListener("click", () => {
            //tasto menu collpsible
            collapsibleMenu.classList.toggle("hidden");

            //update aria-expanded attribute per accessibilità
            const isExpanded = toggleButton.getAttribute("aria-expanded") === "true"; //true == visibile
            toggleButton.setAttribute("aria-expanded", !isExpanded);
        });
    }
});

</script>
