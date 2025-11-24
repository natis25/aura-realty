<?php
require_once '../../config/jwt.php';
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->refresh_token)) {
    http_response_code(400);
    echo json_encode(['error' => 'Token de refresh no enviado']);
    exit;
}

try {
    $decoded = JWT::decode($data->refresh_token, JWT_SECRET, ['HS256']);

    // Crear un nuevo token de acceso
    $payload = [
        'id' => $decoded->id,
        'exp' => time() + JWT_EXPIRATION
    ];

    $new_access = JWT::encode($payload, JWT_SECRET);

    echo json_encode([
        "access_token" => $new_access
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh token inválido']);
    exit;
}
?>
