<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

// Validar JWT y rol
$user = validate_jwt();

// Parámetros opcionales
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1,intval($_GET['limit'])) : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$offset = ($page - 1) * $limit;

// Consulta base
$sql_base = "
    SELECT c.id, c.solicitud_id, c.agente_id, c.fecha, c.hora, c.estado,
           s.usuario_id, u.nombre AS cliente,
           p.id AS propiedad_id, p.titulo AS propiedad
    FROM citas c
    JOIN solicitudes_cita s ON c.solicitud_id = s.id
    JOIN usuarios u ON s.usuario_id = u.id
    JOIN propiedades p ON s.propiedad_id = p.id
";

// Filtros
$conditions = [];
$params = [];
$types = "";

// Rol agente → solo sus citas
if($user->rol === 'agente') {
    $conditions[] = "c.agente_id = ?";
    $params[] = $user->id;
    $types .= "i";
}

// Rol cliente → solo sus citas
if($user->rol === 'cliente') {
    $conditions[] = "s.usuario_id = ?";
    $params[] = $user->id;
    $types .= "i";
}

// Filtrar estado
if($estado) {
    $conditions[] = "c.estado = ?";
    $params[] = $estado;
    $types .= "s";
}

// Búsqueda por propiedad o cliente
if($search) {
    $conditions[] = "(p.titulo LIKE ? OR u.nombre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
$sql = "$sql_base $where ORDER BY c.fecha DESC, c.hora ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$citas = [];
while($row = $result->fetch_assoc()) {
    $citas[] = $row;
}

// Contar total
$count_sql = "SELECT COUNT(*) AS total FROM citas c JOIN solicitudes_cita s ON c.solicitud_id = s.id JOIN usuarios u ON s.usuario_id = u.id JOIN propiedades p ON s.propiedad_id = p.id $where";
$count_stmt = $conn->prepare($count_sql);
if($conditions) $count_stmt->bind_param(substr($types,0,-2), ...array_slice($params,0,-2)); // quitar limit y offset
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];

echo json_encode([
    'citas' => $citas,
    'pagina' => $page,
    'limit' => $limit,
    'total' => intval($total),
    'total_paginas' => ceil($total / $limit)
]);
?>
