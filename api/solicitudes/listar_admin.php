<?php
// API simplificada para solicitudes
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Incluir configuración de base de datos
include_once("../../config/db.php");

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión: " . $conn->connect_error
    ]);
    exit;
}

// Consulta simplificada
$sql = "SELECT * FROM solicitudes_cita ORDER BY fecha_solicitada DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Error SQL: " . $conn->error
    ]);
    exit;
}

$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}

echo json_encode([
    "success" => true,
    "solicitudes" => $solicitudes,
    "total" => count($solicitudes)
]);
?>
