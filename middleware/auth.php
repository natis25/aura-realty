<?php
require_once __DIR__ . '/../config/jwt.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Compatibilidad si getallheaders() no existe
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function validate_jwt($roles_permitidos = null) {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token no proporcionado', 'headers_recibidos' => $headers]);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));

        if ($roles_permitidos !== null) {
            if (is_array($roles_permitidos)) {
                if (!in_array($decoded->rol, $roles_permitidos)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Rol no autorizado']);
                    exit;
                }
            } else {
                if ($decoded->rol !== $roles_permitidos) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Rol no autorizado']);
                    exit;
                }
            }
        }

        return $decoded;

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido o expirado', 'mensaje' => $e->getMessage()]);
        exit;
    }
}
