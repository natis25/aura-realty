<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "inmobiliaria"; // nombre de la base de datos
$port = 3306;

$conn = new mysqli($servername, $username, $password, $database, $port);

// Nota: No terminamos la ejecución aquí para permitir que el script principal maneje el error
?>
