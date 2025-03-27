<html>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <button type="button" onclick="window.location.href='login.php'"
                            class="bg-gray-700 hover:bg-blue-800 text-white rounded-lg px-4 py-2 font-medium focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Login
                    </button>
                </div>
        </div>
    </nav>

    <div class="h-[6rem]"></div>

</body>