<?php
require_once "config/db.php";

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error al conectar: " . $conn->connect_error]);
} else {
    echo json_encode(["success" => true, "message" => "Conexión exitosa"]);
}
?>
