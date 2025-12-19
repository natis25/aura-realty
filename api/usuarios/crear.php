<?php
header("Content-Type: application/json");
require_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['nombre'], $data['correo'], $data['rol_id'], $data['contrasena'])) {
    echo json_encode(["success" => false, "error" => "Faltan datos obligatorios"]);
    exit;
}

// 1. Definir todas las variables correctamente
$nombre = $conn->real_escape_string($data['nombre']);
$correo = $conn->real_escape_string($data['correo']);
$telefono = isset($data['telefono']) ? $conn->real_escape_string($data['telefono']) : ''; // Definida
$rol_id = (int) $data['rol_id'];
$contrasena = $data['contrasena'];

// 2. Verificar duplicado
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "El correo ya está registrado"]);
    exit;
}

// 3. Insertar (Corregido: 5 columnas y 5 signos de interrogación)
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, rol_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $nombre, $correo, $telefono, $contrasena, $rol_id);

if ($stmt->execute()) {
    $usuario_id = $stmt->insert_id;

    // 4. Lógica para agentes (Corregida la variable $usuario_id)
    if ($rol_id === 3) {
        $stmtAg = $conn->prepare("INSERT INTO agentes (usuario_id) VALUES (?)");
        $stmtAg->bind_param("i", $usuario_id);
        $stmtAg->execute();
    }
    
    echo json_encode(["success" => true, "message" => "Usuario creado exitosamente"]);
} else {
    // Si falla el SQL, devuelve el error como JSON
    echo json_encode(["success" => false, "error" => "Error de base de datos: " . $conn->error]);
}