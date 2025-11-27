<?php
header("Content-Type: application/json");
require_once("../../config/db.php");

// Obtener los datos enviados por POST (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'], $data['nombre'], $data['correo'], $data['rol'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

$id = (int)$data['id'];
$nombre = $conn->real_escape_string($data['nombre']);
$correo = $conn->real_escape_string($data['correo']);
$rol = $conn->real_escape_string($data['rol']);
$estado = isset($data['estado']) ? $conn->real_escape_string($data['estado']) : 'activo';

// Verificar que el usuario exista
$stmt = $conn->prepare("SELECT id, rol_id FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    exit;
}
$user = $result->fetch_assoc();
$rol_id_actual = $user['rol_id'];

// Obtener el rol_id del rol nuevo
$stmtRol = $conn->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
$stmtRol->bind_param("s", $rol);
$stmtRol->execute();
$resultRol = $stmtRol->get_result();
if ($resultRol->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Rol no válido"]);
    exit;
}
$rol_id_nuevo = $resultRol->fetch_assoc()['id'];

// Verificar que el correo no esté siendo usado por otro usuario
$stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
$stmtCheck->bind_param("si", $correo, $id);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "El correo ya está en uso por otro usuario"]);
    exit;
}

// Actualizar usuario
$stmtUpdate = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol_id=?, estado=? WHERE id=?");
$stmtUpdate->bind_param("ssisi", $nombre, $correo, $rol_id_nuevo, $estado, $id);

if ($stmtUpdate->execute()) {

    // ================== Manejo de tabla agentes ==================
    // Si antes era agente pero ahora no, eliminar de agentes
    $stmtRolActual = $conn->prepare("SELECT nombre FROM roles WHERE id = ?");
    $stmtRolActual->bind_param("i", $rol_id_actual);
    $stmtRolActual->execute();
    $rolNombreActual = $stmtRolActual->get_result()->fetch_assoc()['nombre'];

    if ($rolNombreActual === "agente" && $rol !== "agente") {
        $stmtDel = $conn->prepare("DELETE FROM agentes WHERE usuario_id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
    }

    // Si antes no era agente y ahora sí, insertar en agentes
    if ($rolNombreActual !== "agente" && $rol === "agente") {
        $stmtIns = $conn->prepare("INSERT INTO agentes (usuario_id) VALUES (?)");
        $stmtIns->bind_param("i", $id);
        $stmtIns->execute();
    }

    echo json_encode(["success" => true, "message" => "Usuario actualizado correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar usuario: ".$conn->error]);
}
