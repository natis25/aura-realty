<?php
// API simplificada para propiedades
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

// Consulta simple
$sql = "SELECT * FROM propiedades ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Error SQL: " . $conn->error
    ]);
    exit;
}

$propiedades = [];
while ($row = $result->fetch_assoc()) {
    // Limpiar y asegurar UTF-8 en cada campo
    $propiedad = [];
    foreach ($row as $key => $value) {
        if (is_string($value)) {
            // Convertir a UTF-8 válido y quitar caracteres problemáticos
            $propiedad[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        } else {
            $propiedad[$key] = $value;
        }
    }
    $propiedades[] = $propiedad;
}

echo json_encode([
    "success" => true,
    "propiedades" => $propiedades
], JSON_UNESCAPED_UNICODE);
?>
?>
