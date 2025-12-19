<?php
header("Content-Type: application/json");

// Evitar que warnings/notices rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once("../../config/db.php");
    require_once("../../middleware/auth.php");

    // Validar JWT y rol cliente
    $decoded = validate_jwt("cliente");
    $usuario_id = $decoded->id;

    $sql = "SELECT nombre, correo, telefono, direccion, ciudad, documento_identidad, fecha_nacimiento
            FROM usuarios
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $perfil = $result->fetch_assoc();

    if (!$perfil) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    echo json_encode($perfil);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno', 'error' => $e->getMessage()]);
}
