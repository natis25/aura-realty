<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['solicitud_id']) || !isset($data['estado'])) {
    echo json_encode(["success"=>false,"message"=>"Faltan datos requeridos"]);
    exit;
}

$solicitud_id = intval($data['solicitud_id']);
$estado = $data['estado'];
$agente_id = isset($data['agente_id']) ? intval($data['agente_id']) : null;
$fecha = isset($data['fecha_solicitada']) ? $data['fecha_solicitada'] : null;
$hora = isset($data['hora_solicitada']) ? $data['hora_solicitada'] : null;
$mensaje = isset($data['mensaje']) ? $data['mensaje'] : null;

$estados_permitidos = ['pendiente','aceptada','rechazada','cancelada','completada','en_progreso'];
if (!in_array($estado,$estados_permitidos)) {
    echo json_encode(["success"=>false,"message"=>"Estado no válido"]);
    exit;
}

// Verificar solicitud existe
$sqlCheck = "SELECT * FROM solicitudes_cita WHERE id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i",$solicitud_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
if($resultCheck->num_rows === 0){
    echo json_encode(["success"=>false,"message"=>"Solicitud no encontrada"]);
    exit;
}

// Construir UPDATE dinámico
$campos = [];
$tipos = "";
$valores = [];

$campos[] = "estado=?";
$tipos .= "s";
$valores[] = $estado;

if ($agente_id !== null) {
    $campos[] = "agente_asignado=?";
    $tipos .= "i";
    $valores[] = $agente_id;
}

if ($fecha !== null) {
    $campos[] = "fecha_solicitada=?";
    $tipos .= "s";
    $valores[] = $fecha;
}

if ($hora !== null) {
    $campos[] = "hora_solicitada=?";
    $tipos .= "s";
    $valores[] = $hora;
}

if ($mensaje !== null) {
    $campos[] = "mensaje=?";
    $tipos .= "s";
    $valores[] = $mensaje;
}

$valores[] = $solicitud_id; // para el WHERE
$tipos .= "i";

$sql = "UPDATE solicitudes_cita SET ".implode(", ", $campos)." WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($tipos, ...$valores);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Solicitud actualizada correctamente"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Error al actualizar solicitud"]);
}

$conn->close();
?>
