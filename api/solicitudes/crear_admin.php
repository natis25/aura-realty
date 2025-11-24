<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

$propiedad_id = $data['propiedad_id'] ?? null;
$usuario_id   = $data['usuario_id'] ?? null;
$fecha        = $data['fecha_solicitada'] ?? null;
$hora         = $data['hora_solicitada'] ?? null;
$mensaje      = $data['mensaje'] ?? "";

if(!$propiedad_id || !$fecha || !$hora){
    echo json_encode(["success"=>false,"message"=>"Faltan datos obligatorios"]);
    exit;
}

// Si no hay usuario_id, asignar NULL
$usuario_id = $usuario_id ?: null;

$creada_por = "admin";
$estado = "pendiente";

$sql = "INSERT INTO solicitudes_cita 
        (propiedad_id, usuario_id, fecha_solicitada, hora_solicitada, mensaje, creada_por, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisssss", $propiedad_id, $usuario_id, $fecha, $hora, $mensaje, $creada_por, $estado);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Cita creada correctamente"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Error al crear cita","error"=>$stmt->error]);
}

$conn->close();
?>
