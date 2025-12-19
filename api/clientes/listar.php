<?php
// API simplificada para clientes
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Asegurar encoding UTF-8
if (!mb_internal_encoding("UTF-8")) {
    mb_internal_encoding("UTF-8");
}

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

// Consulta simple - todos los usuarios (para que haya opciones disponibles)
$sql = "SELECT id, nombre FROM usuarios ORDER BY nombre";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Error SQL: " . $conn->error
    ]);
    exit;
}

$clientes = [];
while ($row = $result->fetch_assoc()) {
    // Limpiar y asegurar UTF-8
    $cliente = [];
    foreach ($row as $key => $value) {
        if (is_string($value)) {
            $cliente[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        } else {
            $cliente[$key] = $value;
        }
    }
    $clientes[] = $cliente;
}

echo json_encode([
    "success" => true,
    "clientes" => $clientes
], JSON_UNESCAPED_UNICODE);
?>
?>
