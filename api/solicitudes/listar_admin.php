<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

try {

    $sql = "
        SELECT 
            s.id,
            s.propiedad_id,
            p.titulo AS propiedad_titulo,
            s.usuario_id,
            u.nombre AS cliente_nombre,
            s.fecha_solicitada,
            s.hora_solicitada,
            s.estado,
            s.mensaje,
            s.creada_por,
            s.agente_asignado AS agente_id,
            ua.nombre AS agente_nombre
        FROM solicitudes_cita s
        INNER JOIN propiedades p ON s.propiedad_id = p.id
        INNER JOIN usuarios u ON s.usuario_id = u.id
        LEFT JOIN agentes a ON s.agente_asignado = a.id
        LEFT JOIN usuarios ua ON a.usuario_id = ua.id
        ORDER BY s.fecha_solicitada DESC, s.hora_solicitada DESC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("SQL Error: " . $conn->error);
    }

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
        "message" => "Error en la consulta SQL",
        "sql_error" => $e->getMessage()
    ]);
}
?>
