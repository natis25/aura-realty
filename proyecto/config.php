<?php
// config.php - conectar a la BD Droc (para pruebas)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$pass = '';         // en XAMPP por defecto root no tiene contraseña
$dbname = 'Droca';   // <- tu base de datos en phpMyAdmin

$conexion = new mysqli($host, $user, $pass, $dbname);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
} else {
    echo ".";
}
