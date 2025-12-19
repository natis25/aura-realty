<?php
header("Content-Type: application/json");
require_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

// Cambiado: Validamos 'rol_id' en lugar de 'rol'
if (!$data || !isset($data['nombre'], $data['correo'], $data['rol_id'], $data['contrasena'])) {
    echo json_encode(["success" => false, "error" => "Faltan datos obligatorios"]);
    exit;
}

$nombre = $conn->real_escape_string($data['nombre']);
$correo = $conn->real_escape_string($data['correo']);
$rol_id = (int)$data['rol_id']; // Recibimos el ID directamente del select
$contrasena = $data['contrasena'];

// Verificar duplicado
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "El correo ya está registrado"]);
    exit;
}

// Insertar
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $nombre, $correo, $contrasena, $rol_id);

if ($stmt->execute()) {
    $usuario_id = $stmt->insert_id;

    // Lógica para agentes (Rol ID 3 es Agente según tu SQL)
    if ($rol_id === 3) {
        $stmtAgente = $conn->prepare("INSERT INTO agentes (usuario_id) VALUES (?)");
        $stmtAgente->bind_param("i", $usuario_id);
        $stmtAgente->execute();
    }
    // IMPORTANTE: Tu JS busca 'success', asegúrate de enviarlo
    echo json_encode(["success" => true, "message" => "Usuario creado"]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}