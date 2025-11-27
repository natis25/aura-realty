<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

$rol = isset($_GET['rol']) ? $_GET['rol'] : null; // <-- Filtrar por rol
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    $sql = "
        SELECT u.id, u.nombre, u.correo, u.estado, r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE 1
    ";

    $params = [];
    $types = "";

    if ($rol) {
        $sql .= " AND r.nombre = ?";
        $params[] = $rol;
        $types .= "s";
    }

    if ($search) {
        $sql .= " AND (u.nombre LIKE ? OR u.correo LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }

    $sql .= " ORDER BY u.id ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    // Contar total para paginación
    $countSql = "SELECT COUNT(*) as total FROM usuarios u INNER JOIN roles r ON u.rol_id = r.id WHERE 1";
    $countParams = [];
    $countTypes = "";

    if ($rol) {
        $countSql .= " AND r.nombre = ?";
        $countParams[] = $rol;
        $countTypes .= "s";
    }

    if ($search) {
        $countSql .= " AND (u.nombre LIKE ? OR u.correo LIKE ?)";
        $countParams[] = "%$search%";
        $countParams[] = "%$search%";
        $countTypes .= "ss";
    }

    $countStmt = $conn->prepare($countSql);
    if (!empty($countParams)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $total = $totalResult['total'];
    $totalPaginas = ceil($total / $limit);

    echo json_encode([
        "success" => true,
        "usuarios" => $usuarios,
        "pagina" => $page,
        "total_paginas" => $totalPaginas
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar usuarios",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>
