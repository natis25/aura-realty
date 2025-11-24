<?php
// C:\xampp\htdocs\TALLER\aura-realty\api\solicitudes\crear.php

// Mostrar errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Conexión a DB
require_once "../../config/db.php";

// Obtener payload JSON
$input = json_decode(file_get_contents("php://input"), true);

// Validar campos requeridos
if (
    !isset($input['usuario_id']) || 
    !isset($input['propiedad_id']) || 
    !isset($input['fecha_solicitada']) || 
    !isset($input['hora_solicitada'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos obligatorios"
    ]);
    exit;
}

// Asignar variables
$usuario_id = (int)$input['usuario_id'];
$propiedad_id = (int)$input['propiedad_id'];
$fecha_solicitada = $input['fecha_solicitada'];
$hora_solicitada = $input['hora_solicitada'];
$mensaje = $input['mensaje'] ?? null;

// Validar formato de fecha y hora
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha_solicitada)) {
    echo json_encode(["success"=>false,"message"=>"Formato de fecha inválido"]);
    exit;
}
if (!preg_match("/^\d{2}:\d{2}$/", $hora_solicitada)) {
    echo json_encode(["success"=>false,"message"=>"Formato de hora inválido (HH:MM)"]);
    exit;
}
$hora_solicitada .= ":00"; // Convertir a HH:MM:SS

try {
    // Verificar que no exista una solicitud con la misma propiedad, fecha y hora
    $checkSql = "SELECT id FROM solicitudes_cita WHERE propiedad_id=? AND fecha_solicitada=? AND hora_solicitada=?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("iss", $propiedad_id, $fecha_solicitada, $hora_solicitada);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if ($resultCheck->num_rows > 0) {
        echo json_encode([
            "success"=>false,
            "message"=>"Ya existe una solicitud para esta propiedad en la fecha y hora seleccionadas"
        ]);
        exit;
    }

    // Insertar nueva solicitud
    $insertSql = "INSERT INTO solicitudes_cita 
        (usuario_id, propiedad_id, fecha_solicitada, hora_solicitada, mensaje, creada_por) 
        VALUES (?, ?, ?, ?, ?, 'cliente')";
    $stmtInsert = $conn->prepare($insertSql);
    $stmtInsert->bind_param("iisss", $usuario_id, $propiedad_id, $fecha_solicitada, $hora_solicitada, $mensaje);
    $stmtInsert->execute();

    if ($stmtInsert->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Solicitud creada con éxito"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No se pudo crear la solicitud"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al procesar la solicitud: ".$e->getMessage()
    ]);
}

$conn->close();
?>
