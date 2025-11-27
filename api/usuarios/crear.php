<?php
header("Content-Type: application/json");
require_once("../../config/db.php");

// Obtener los datos enviados por POST (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['nombre'], $data['correo'], $data['rol'], $data['contrasena'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

$nombre = $conn->real_escape_string($data['nombre']);
$correo = $conn->real_escape_string($data['correo']);
$rol = $conn->real_escape_string($data['rol']);
$contrasena = $data['contrasena']; // <-- texto plano, compatible con tu login actual

// Verificar que el correo no exista
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "El correo ya está registrado"]);
    exit;
}

// Obtener el rol_id
$stmt = $conn->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
$stmt->bind_param("s", $rol);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Rol no válido"]);
    exit;
}
$rol_id = $result->fetch_assoc()['id'];

// Insertar en usuarios
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $nombre, $correo, $contrasena, $rol_id);

if ($stmt->execute()) {
    $usuario_id = $stmt->insert_id;

    // Si el rol es agente, insertar también en tabla agentes
    if ($rol === "agente") {
        $stmtAgente = $conn->prepare("INSERT INTO agentes (usuario_id) VALUES (?)");
        $stmtAgente->bind_param("i", $usuario_id);
        if (!$stmtAgente->execute()) {
            echo json_encode(["success" => false, "message" => "Usuario creado pero no se pudo registrar como agente: ".$conn->error]);
            exit;
        }
    }

    echo json_encode(["success" => true, "message" => "Usuario creado correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al crear usuario: ".$conn->error]);
}
