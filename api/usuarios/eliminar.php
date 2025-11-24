<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

// Validar JWT y rol
$admin = validate_jwt('admin');

// Leer datos del POST
$data = json_decode(file_get_contents("php://input"));

if(!isset($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID del usuario']);
    exit;
}

// Verificar que el usuario exista
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $data->id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}

// Eliminar usuario
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $data->id);

if($stmt->execute()) {
    echo json_encode(['status' => 'success', 'mensaje' => 'Usuario eliminado correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar el usuario']);
}
?>
