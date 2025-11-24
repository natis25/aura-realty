<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

try {
    // Verificar que se pase el ID del agente
    if (!isset($_GET['agente_id'])) {
        echo json_encode([
            "success" => false,
            "message" => "Falta el ID del agente"
        ]);
        exit;
    }

    $agente_id = intval($_GET['agente_id']);

    $sql = "
        SELECT 
            s.id,
            s.fecha_solicitada,
            s.hora_solicitada,
            s.estado,
            s.mensaje,
            p.titulo AS propiedad_titulo,
            u.nombre AS cliente_nombre
        FROM solicitudes_cita s
        INNER JOIN propiedades p ON s.propiedad_id = p.id
        INNER JOIN usuarios u ON s.usuario_id = u.id
        WHERE s.agente_asignado = ?
        ORDER BY s.fecha_solicitada DESC, s.hora_solicitada DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $agente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }

    echo json_encode([
        "success" => true,
        "solicitudes" => $solicitudes
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar solicitudes del agente",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>
