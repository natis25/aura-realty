<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once("../../config/db.php");

if (!isset($_GET['usuario_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "usuario_id requerido",
        "agente_id" => null
    ]);
    exit;
}

$usuario_id = intval($_GET['usuario_id']);

$sql = "SELECT id FROM agentes WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "No existe un agente asociado a este usuario",
        "agente_id" => null
    ]);
} else {
    $row = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "agente_id" => $row["id"]
    ]);
}

$stmt->close();
$conn->close();
?>
