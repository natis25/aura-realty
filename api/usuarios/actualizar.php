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
$stmt = $conn->prepare("SELECT id, correo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $data->id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}

// Validar correo único si se actualiza
if(isset($data->correo) && $data->correo !== $user['correo']) {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $data->correo);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'El correo ya está registrado']);
        exit;
    }
}

// Construir consulta dinámicamente
$fields = [];
$params = [];
$types = '';

if(isset($data->nombre)) { $fields[] = 'nombre = ?'; $params[] = $data->nombre; $types .= 's'; }
if(isset($data->correo)) { $fields[] = 'correo = ?'; $params[] = $data->correo; $types .= 's'; }
if(isset($data->rol_id)) { $fields[] = 'rol_id = ?'; $params[] = $data->rol_id; $types .= 'i'; }
if(isset($data->contrasena)) { 
    $fields[] = 'contrasena = ?'; 
    $params[] = password_hash($data->contrasena, PASSWORD_DEFAULT); 
    $types .= 's'; 
}

if(empty($fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay campos para actualizar']);
    exit;
}

$sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
$params[] = $data->id;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if($stmt->execute()) {
    echo json_encode(['status' => 'success', 'mensaje' => 'Usuario actualizado correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el usuario']);
}
?>
