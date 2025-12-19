<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once("../../config/db.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["email"]) || !isset($input["password"])) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

$email = $input["email"];
$password = $input["password"];

$sql = "
SELECT u.id, u.nombre, u.correo, u.contrasena, r.nombre AS rol
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
WHERE u.correo = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    exit;
}

$user = $result->fetch_assoc();

if ($password !== $user["contrasena"]) {
    echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    exit;
}

$token = bin2hex(openssl_random_pseudo_bytes(16));

echo json_encode([
    "success" => true,
    "token" => $token,
    "user" => [
        "id" => $user["id"],
        "nombre" => $user["nombre"],
        "correo" => $user["correo"],
        "rol" => $user["rol"]  // ← ahora sí devuelve: admin / cliente / agente
    ]
]);
