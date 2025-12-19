<?php
header("Content-Type: application/json");
require_once "../../config/db.php";

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT id, nombre, correo, telefono, rol_id FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_assoc());