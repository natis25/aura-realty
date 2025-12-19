<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);


// Validar JWT
$user = validate_jwt();

// Leer datos del POST
$data = json_decode(file_get_contents("php://input"));
if(!isset($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID de la cita']);
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

// Verificar permisos
if($user->rol === 'agente' && $cita['agente_id'] != $user->id) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado para cancelar esta cita']);
    exit;
}

// Cancelar cita
$update = $conn->prepare("UPDATE citas SET estado = 'cancelada' WHERE id = ?");
$update->bind_param("i", $data->id);

if($update->execute()) {
    echo json_encode(['status' => 'success', 'mensaje' => 'Cita cancelada correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cancelar la cita']);
}
?>
