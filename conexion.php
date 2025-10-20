<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "citas"; // ⚙️ Nuevo nombre de la base de datos
$port = 3307;

$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
