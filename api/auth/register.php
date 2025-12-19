<?php
header("Content-Type: application/json");
include_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

$nombre = $data['nombre'];
$correo = $data['correo'];
$telefono = $data['telefono'];
$contrasena = $data['contrasena'];
$rol_id = $data['rol_id'];

//Verificar si el correo ya existe
$checkEmail = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$checkEmail->bind_param("s", $correo);
$checkEmail->execute();
if ($checkEmail->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "El correo ya está registrado"]);
    exit;
}

//Insertar en tabla usuarios
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, rol_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $nombre, $correo, $telefono, $contrasena, $rol_id);

if ($stmt->execute()) {
    $nuevo_id = $stmt->insert_id;

    //Si el rol es agente crear registro en tabla agentes
    if ($rol_id == 3) {
        $stmtAgente = $conn->prepare("INSERT INTO agentes (usuario_id, especialidad) VALUES (?, 'General')");
        $stmtAgente->bind_param("i", $nuevo_id);
        $stmtAgente->execute();
    }

    echo json_encode(["success" => true, "message" => "Usuario registrado correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar: " . $conn->error]);
}
?>