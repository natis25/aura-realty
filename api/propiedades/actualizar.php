<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

// Validar JWT y rol (solo admin)
$admin = validate_jwt('admin');

// Leer datos del POST
$data = json_decode(file_get_contents("php://input"));

if(!isset($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID de la propiedad']);
    exit;
}

// Construir SQL dinámico según campos enviados
$fields = [];
$params = [];
$types = '';

$allowed_fields = ['titulo','direccion','ciudad','tipo','precio','area','habitaciones','banos','descripcion','imagen_principal','disponible'];

foreach($allowed_fields as $field) {
    if(isset($data->$field)) {
        $fields[] = "$field = ?";
        $params[] = $data->$field;
        $types .= ($field == 'habitaciones' || $field == 'banos') ? 'i' : (($field == 'precio' || $field == 'area') ? 'd' : 's');
    }
}

if(empty($fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No se proporcionaron campos a actualizar']);
    exit;
}

$params[] = $data->id;
$types .= 'i';

$sql = "UPDATE propiedades SET " . implode(', ', $fields) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if($stmt->execute()) {
    echo json_encode(['status' => 'success', 'mensaje' => 'Propiedad actualizada correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la propiedad']);
}
?>
