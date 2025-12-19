<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

// Obtener los nombres en lugar de IDs
$propiedad_nombre = trim(isset($data['propiedad_nombre']) ? $data['propiedad_nombre'] : '');
$cliente_nombre = trim(isset($data['cliente_nombre']) ? $data['cliente_nombre'] : '');
$fecha = isset($data['fecha_solicitada']) ? $data['fecha_solicitada'] : null;
$hora = isset($data['hora_solicitada']) ? $data['hora_solicitada'] : null;
$mensaje = isset($data['mensaje']) ? $data['mensaje'] : "";

if(!$propiedad_nombre || !$fecha || !$hora){
    echo json_encode(["success"=>false,"message"=>"Faltan datos obligatorios"]);
    exit;
}

// Buscar o crear propiedad por nombre
$propiedad_id = null;
$sql_prop = "SELECT id FROM propiedades WHERE titulo = ? LIMIT 1";
$stmt_prop = $conn->prepare($sql_prop);
$stmt_prop->bind_param("s", $propiedad_nombre);
$stmt_prop->execute();
$result_prop = $stmt_prop->get_result();

if($result_prop->num_rows > 0){
    // Propiedad existe
    $propiedad_id = $result_prop->fetch_assoc()['id'];
} else {
    // Crear nueva propiedad
    $sql_insert_prop = "INSERT INTO propiedades (titulo, disponible, creado_en) VALUES (?, 1, NOW())";
    $stmt_insert_prop = $conn->prepare($sql_insert_prop);
    $stmt_insert_prop->bind_param("s", $propiedad_nombre);
    if($stmt_insert_prop->execute()){
        $propiedad_id = $conn->insert_id;
    } else {
        echo json_encode(["success"=>false,"message"=>"Error al crear propiedad"]);
        exit;
    }
}

// Buscar o crear cliente por nombre
$usuario_id = null;
if($cliente_nombre){
    $sql_user = "SELECT id FROM usuarios WHERE nombre = ? LIMIT 1";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("s", $cliente_nombre);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if($result_user->num_rows > 0){
        // Usuario existe
        $usuario_id = $result_user->fetch_assoc()['id'];
    } else {
        // Crear nuevo usuario (cliente)
        $sql_insert_user = "INSERT INTO usuarios (nombre, correo, contrasena, rol_id, estado, creado_en)
                           VALUES (?, ?, 'cliente123', 2, 'activo', NOW())";
        $correo_temp = strtolower(str_replace(' ', '.', $cliente_nombre)) . '@cliente.com';
        $stmt_insert_user = $conn->prepare($sql_insert_user);
        $stmt_insert_user->bind_param("ss", $cliente_nombre, $correo_temp);
        if($stmt_insert_user->execute()){
            $usuario_id = $conn->insert_id;
        }
    }
}

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
