<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

// Validar JWT y rol
$user = validate_jwt();
if(!in_array($user->rol, ['admin','agente'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Rol no autorizado']);
    exit;
}

// Leer datos del POST
$data = json_decode(file_get_contents("php://input"));
if(!isset($data->id, $data->estado)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

$estados_permitidos = ['programada','en_progreso','finalizada','cancelada'];
if(!in_array($data->estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido']);
    exit;
}

// Verificar que la cita exista
$stmt = $conn->prepare("SELECT id, agente_id FROM citas WHERE id = ?");
$stmt->bind_param("i", $data->id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Cita no encontrada']);
    exit;
}
$cita = $result->fetch_assoc();

// Si es agente, solo puede actualizar si es su cita
if($user->rol === 'agente' && $cita['agente_id'] != $user->id) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado para esta cita']);
    exit;
}

// Actualizar estado
$update = $conn->prepare("UPDATE citas SET estado = ? WHERE id = ?");
$update->bind_param("si", $data->estado, $data->id);

if($update->execute()) {
    echo json_encode(['status' => 'success', 'mensaje' => 'Estado actualizado correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar estado']);
}
?>
