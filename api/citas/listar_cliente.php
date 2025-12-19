<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // Evitar mostrar errores PHP en JSON

try {
    // Validar JWT y obtener usuario
    $user = validate_jwt();

    if($user->rol !== 'cliente') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    // Parámetros opcionales
    $page  = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1,intval($_GET['limit'])) : 10;
    $offset = ($page - 1) * $limit;

    // Consulta base: solo citas aceptadas del cliente
    $sql = "
        SELECT 
            c.id AS cita_id,
            c.solicitud_id,
            c.agente_id,
            c.fecha,
            c.hora,
            c.estado,
            s.usuario_id,
            u.nombre AS cliente,
            p.id AS propiedad_id,
            p.titulo AS propiedad
        FROM citas c
        JOIN solicitudes_cita s ON c.solicitud_id = s.id
        JOIN usuarios u ON s.usuario_id = u.id
        JOIN propiedades p ON s.propiedad_id = p.id
        WHERE s.usuario_id = ? AND c.estado = 'aceptada'
        ORDER BY c.fecha DESC, c.hora ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user->id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $citas = [];
    while($row = $result->fetch_assoc()) {
        $citas[] = $row;
    }

    // Contar total de citas aceptadas
    $count_sql = "
        SELECT COUNT(*) AS total
        FROM citas c
        JOIN solicitudes_cita s ON c.solicitud_id = s.id
        WHERE s.usuario_id = ? AND c.estado = 'aceptada'
    ";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user->id);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'citas' => $citas,
        'pagina' => $page,
        'limit' => $limit,
        'total' => intval($total),
        'total_paginas' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}
$conn->close();
?>
