<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

// Obtener ID de usuario
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if (!$usuario_id) {
    echo json_encode(["success" => false, "message" => "Falta el ID del usuario"]);
    exit;
}

// Filtros opcionales
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$propiedad_id = isset($_GET['propiedad_id']) ? intval($_GET['propiedad_id']) : 0;

try {
    $sql = "
        SELECT 
            s.id,
            s.fecha_solicitada,
            s.hora_solicitada,
            s.estado,
            s.mensaje,
            p.titulo AS propiedad_titulo,
            COALESCE(ua.nombre, '') AS agente_nombre
        FROM solicitudes_cita s
        INNER JOIN propiedades p ON s.propiedad_id = p.id
        LEFT JOIN agentes a ON s.agente_asignado = a.id
        LEFT JOIN usuarios ua ON a.usuario_id = ua.id
        WHERE s.usuario_id = ?
    ";

    $params = [$usuario_id];
    $types = "i";

    // Filtro por estado
    if (!empty($estado)) {
        $sql .= " AND s.estado = ?";
        $params[] = $estado;
        $types .= "s";
    }

    // Filtro por propiedad
    if ($propiedad_id > 0) {
        $sql .= " AND p.id = ?";
        $params[] = $propiedad_id;
        $types .= "i";
    }

    $sql .= " ORDER BY s.fecha_solicitada DESC, s.hora_solicitada DESC";

    $stmt = $conn->prepare($sql);
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }

    echo json_encode(["success" => true, "solicitudes" => $solicitudes]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar solicitudes",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>
