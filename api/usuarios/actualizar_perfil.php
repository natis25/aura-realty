<?php
header("Content-Type: application/json");

// Evitar que warnings/notices rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once("../../config/db.php");
    require_once("../../middleware/auth.php");

    $decoded = validate_jwt("cliente");
    $usuario_id = $decoded->id;

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
        exit;
    }

    $nombre = $input['nombre'] ?? null;
    $telefono = $input['telefono'] ?? null;
    $direccion = $input['direccion'] ?? null;
    $ciudad = $input['ciudad'] ?? null;
    $documento_identidad = $input['documento_identidad'] ?? null;
    $fecha_nacimiento = $input['fecha_nacimiento'] ?? null;

    $sql = "UPDATE usuarios SET 
                nombre = ?, 
                telefono = ?, 
                direccion = ?, 
                ciudad = ?, 
                documento_identidad = ?, 
                fecha_nacimiento = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssi",
        $nombre,
        $telefono,
        $direccion,
        $ciudad,
        $documento_identidad,
        $fecha_nacimiento,
        $usuario_id
    );

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar perfil', 'error' => $e->getMessage()]);
}
