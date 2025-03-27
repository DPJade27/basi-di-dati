<?php

//connessione al db
$conn = mysqli_connect('localhost:3306', 'root', '', 'depaolis_giada');

//controllo connessione
if (!$conn) {
    echo 'Connection error: ' . mysqli_connect_error();
}

//codifica char
mysqli_set_charset($conn, "utf-8");

?>