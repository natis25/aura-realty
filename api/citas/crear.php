<?php
require_once '../../config/db.php';
require_once '../../middleware/auth.php';
require_once '../../helpers/validar_horario.php'; // función para evitar choques de horarios

header('Content-Type: application/json');

// Validar JWT (admin o agente)
$user = validate_jwt();
if(!in_array($user->rol, ['admin','agente'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Rol no autorizado']);
    exit;
}

// Leer datos del POST
$data = json_decode(file_get_contents("php://input"));
if(!isset($data->solicitud_id, $data->fecha, $data->hora)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Verificar que la solicitud exista y esté aceptada
$stmt = $conn->prepare("SELECT * FROM solicitudes_cita WHERE id = ?");
$stmt->bind_param("i", $data->solicitud_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Solicitud no encontrada']);
    exit;
}
$solicitud = $result->fetch_assoc();
if($solicitud['estado'] !== 'aceptada') {
    http_response_code(400);
    echo json_encode(['error' => 'La solicitud no está aceptada']);
    exit;
}

// Validar choques de horario
$agente_id = $solicitud['agente_asignado'];
if(!validar_horario($conn, $agente_id, $data->fecha, $data->hora)) {
    http_response_code(400);
    echo json_encode(['error' => 'El agente no está disponible en ese horario']);
    exit;
}

// Crear cita
$insert = $conn->prepare("INSERT INTO citas (solicitud_id, agente_id, fecha, hora) VALUES (?, ?, ?, ?)");
$insert->bind_param("iiss", $data->solicitud_id, $agente_id, $data->fecha, $data->hora);

if($insert->execute()) {
    // Opcional: actualizar estado de la solicitud a "en_progreso"
    $update = $conn->prepare("UPDATE solicitudes_cita SET estado = 'en_progreso' WHERE id = ?");
    $update->bind_param("i", $data->solicitud_id);
    $update->execute();

    echo json_encode([
        'status' => 'success',
        'mensaje' => 'Cita creada correctamente',
        'cita_id' => $insert->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear la cita']);
}
?>
