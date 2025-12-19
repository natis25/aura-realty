<?php
header("Content-Type: application/json");
require_once "../../config/db.php";

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Borrado lógico: Solo traemos los 'activos'
$query = "SELECT u.id, u.nombre, u.correo, u.telefono, r.nombre as rol, u.rol_id 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          WHERE u.estado = 'activo' AND (u.nombre LIKE ? OR u.correo LIKE ?)";

$stmt = $conn->prepare($query);
$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);