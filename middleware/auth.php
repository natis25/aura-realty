<?php
//require_once '../config/jwt.php';
require_once __DIR__ . '/../config/jwt.php';
//no hay archivo require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function validate_jwt($roles_permitidos = null) {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token no proporcionado']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));

        // Si se especifican roles, validar
        if ($roles_permitidos !== null) {
            if (is_array($roles_permitidos)) {
                if (!in_array($decoded->rol, $roles_permitidos)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Rol no autorizado']);
                    exit;
                }
            } else {
                // Solo un rol
                if ($decoded->rol !== $roles_permitidos) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Rol no autorizado']);
                    exit;
                }
            }
        }

        return $decoded; // Información del usuario dentro del token

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido o expirado']);
        exit;
    }
}
?>
