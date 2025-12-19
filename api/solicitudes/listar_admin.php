<?php
// Limpiar cualquier output anterior
ob_clean();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Headers CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

try {
    include_once("../../config/db.php");
    error_log("listar_admin.php: DB incluido");
} catch (Exception $e) {
    error_log("listar_admin.php: Error incluyendo DB: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error de configuración: " . $e->getMessage()
    ]);
    exit;
}

try {
    // Verificar conexión
    if ($conn->connect_error) {
        error_log("listar_admin.php: Error de conexión: " . $conn->connect_error);
        echo json_encode([
            "success" => false,
            "message" => "Error de conexión a BD: " . $conn->connect_error
        ]);
        exit;
    }

    error_log("listar_admin.php: Conexión exitosa");

    // Consulta completa
    $sql = "SELECT s.id, s.propiedad_id, s.usuario_id, s.fecha_solicitada, s.hora_solicitada, s.estado, s.mensaje, s.creada_por, s.agente_asignado, p.titulo AS propiedad_titulo FROM solicitudes_cita s INNER JOIN propiedades p ON s.propiedad_id = p.id ORDER BY s.fecha_solicitada DESC, s.hora_solicitada DESC";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("listar_admin.php: Error SQL: " . $conn->error);
        echo json_encode([
            "success" => false,
            "message" => "Error en consulta SQL",
            "sql_error" => $conn->error
        ]);
        exit;
    }

    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = [
            'id' => $row['id'],
            'propiedad_titulo' => $row['propiedad_titulo'],
            'cliente_nombre' => 'Cliente', // Placeholder por ahora
            'fecha_solicitada' => $row['fecha_solicitada'],
            'hora_solicitada' => $row['hora_solicitada'],
            'estado' => $row['estado'],
            'mensaje' => $row['mensaje'],
            'agente_nombre' => $row['agente_asignado'] ? 'Agente' : '-', // Placeholder por ahora
            'creada_por' => $row['creada_por'],
            'agente_id' => $row['agente_asignado']
        ];
    }

    $response = json_encode([
        "success" => true,
        "solicitudes" => $solicitudes,
        "total" => count($solicitudes)
    ]);

    error_log("listar_admin.php: Respuesta generada, longitud: " . strlen($response));
    echo $response;

} catch (Exception $e) {
    error_log("listar_admin.php: Excepción: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor: " . $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("listar_admin.php: Error fatal: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error fatal del servidor: " . $e->getMessage()
    ]);
}
?>
