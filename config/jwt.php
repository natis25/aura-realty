<?php
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;

define('JWT_SECRET', 'AURA_REAL_SECRET_2025'); 
define('JWT_EXPIRATION', 3600); // 1 hora
define('JWT_REFRESH_EXPIRATION', 604800); // 7 días
?>
