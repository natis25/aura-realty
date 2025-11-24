<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if (!$usuario_id) {
    echo json_encode(["success" => false, "message" => "Falta el ID del usuario"]);
    exit;
}

try {
    $sql = "
        SELECT 
            s.id,
            s.fecha_solicitada,
            s.hora_solicitada,
            s.estado,
            s.mensaje,
            p.titulo AS propiedad_titulo,
            ua.nombre AS agente_nombre
        FROM solicitudes_cita s
        INNER JOIN propiedades p ON s.propiedad_id = p.id
        LEFT JOIN agentes a ON s.agente_asignado = a.id
        LEFT JOIN usuarios ua ON a.usuario_id = ua.id
        WHERE s.usuario_id = ?
        ORDER BY s.fecha_solicitada DESC, s.hora_solicitada DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
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
