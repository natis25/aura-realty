<?php
header('Content-Type: application/json');
include_once("../../config/db.php");
include_once("../auth/session_check.php");

$user = getUserFromToken();
if ($user['rol'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$solicitud_id = $input['solicitud_id'] ?? null;
$agente_id = $input['agente_id'] ?? null;

if (!$solicitud_id || !$agente_id) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$stmt = $conn->prepare("UPDATE solicitudes_cita SET agente_asignado = ? WHERE id = ?");
$stmt->bind_param("ii", $agente_id, $solicitud_id);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Agente asignado"]);
?>
