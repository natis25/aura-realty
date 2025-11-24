<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

// Validar JWT y rol
$admin = validate_jwt('admin');

// Parámetros opcionales para paginación y búsqueda
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'desc' ? 'DESC' : 'ASC';

// Columnas permitidas para ordenar
$allowed_sort = ['id', 'nombre', 'correo', 'rol'];
if(!in_array($sort_by, $allowed_sort)) $sort_by = 'id';

// Calcular offset
$offset = ($page - 1) * $limit;

// Consulta principal con búsqueda, orden y paginación
$sql = "SELECT u.id, u.nombre, u.correo, r.nombre AS rol 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        WHERE u.nombre LIKE ? OR u.correo LIKE ? 
        ORDER BY $sort_by $sort_order
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$like_search = "%$search%";
$stmt->bind_param("ssii", $like_search, $like_search, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

// Contar total de resultados para paginación
$count_sql = "SELECT COUNT(*) AS total FROM usuarios u WHERE u.nombre LIKE ? OR u.correo LIKE ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("ss", $like_search, $like_search);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];

echo json_encode([
    'usuarios' => $usuarios,
    'pagina' => $page,
    'limit' => $limit,
    'total' => intval($total),
    'total_paginas' => ceil($total / $limit)
]);
?>
