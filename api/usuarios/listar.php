<?php
header("Content-Type: application/json");
require_once "../../config/db.php";

$search = isset($_GET['search']) ? $_GET['search'] : '';
$rol_id = isset($_GET['rol_id']) ? $_GET['rol_id'] : '';

// 1. Definimos la base de la consulta (sin los filtros de búsqueda aún)
$query = "SELECT u.id, u.nombre, u.correo, u.telefono, r.nombre as rol, u.rol_id 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          WHERE u.estado = 'activo'";

$params = [];
$types = "";

// 2. Filtro de búsqueda (Nombre o Correo)
if ($search !== '') {
    $query .= " AND (u.nombre LIKE ? OR u.correo LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// 3. Filtro de Rol
if ($rol_id !== '') {
    $query .= " AND u.rol_id = ?";
    $params[] = (int)$rol_id;
    $types .= "i";
}

// 4. Preparación y ejecución DINÁMICA
$stmt = $conn->prepare($query);

// Solo hacemos bind si hay parámetros para evitar errores
if (!empty($params)) {
    // Usamos el operador de "desempaquetado" (...) para pasar el array de parámetros
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);