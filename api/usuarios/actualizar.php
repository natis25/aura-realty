<?php
header("Content-Type: application/json");
require_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'], $data['nombre'], $data['correo'], $data['rol_id'])) {
    echo json_encode(["success" => false, "error" => "Faltan datos obligatorios"]);
    exit;
}

$id = (int)$data['id'];
$nombre = $conn->real_escape_string($data['nombre']);
$correo = $conn->real_escape_string($data['correo']);
$telefono = $conn->real_escape_string($data['telefono']); // <-- Capturamos el teléfono
$rol_id_nuevo = (int)$data['rol_id'];
$estado = isset($data['estado']) ? $conn->real_escape_string($data['estado']) : 'activo';

// 1. Obtener rol actual para manejo de tabla agentes
$stmt = $conn->prepare("SELECT rol_id FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
    exit;
}
$rol_id_actual = $res->fetch_assoc()['rol_id'];

// 2. Actualizar Usuario incluyendo el teléfono
$stmtUpdate = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=?, rol_id=?, estado=? WHERE id=?");
$stmtUpdate->bind_param("sssisi", $nombre, $correo, $telefono, $rol_id_nuevo, $estado, $id);

if ($stmtUpdate->execute()) {
    // Manejo de tabla agentes (ID 3 = Agente)
    if ($rol_id_actual === 3 && $rol_id_nuevo !== 3) {
        $stmtDel = $conn->prepare("DELETE FROM agentes WHERE usuario_id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
    }
    if ($rol_id_actual !== 3 && $rol_id_nuevo === 3) {
        $stmtIns = $conn->prepare("INSERT INTO agentes (usuario_id) VALUES (?)");
        $stmtIns->bind_param("i", $id);
        $stmtIns->execute();
    }
    echo json_encode(["success" => true, "message" => "Usuario actualizado correctamente"]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}