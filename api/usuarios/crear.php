<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

// Validar JWT y rol
$admin = validate_jwt('admin');

// Leer datos del POST
$data = json_decode(file_get_contents("php://input"));

if(!isset($data->nombre, $data->correo, $data->contrasena, $data->rol_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Validar que no exista un usuario con el mismo correo
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $data->correo);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'El correo ya está registrado']);
    exit;
}

// Hashear la contraseña
$hashed_password = password_hash($data->contrasena, PASSWORD_DEFAULT);

// Insertar usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $data->nombre, $data->correo, $hashed_password, $data->rol_id);

if($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'mensaje' => 'Usuario creado correctamente',
        'usuario_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el usuario']);
}
?>
