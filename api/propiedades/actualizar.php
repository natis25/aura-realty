<?php
// Desactivar salida de errores HTML para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

try {
    // 1. Verificar archivos requeridos
    if (!file_exists('../../config/db.php')) throw new Exception("Falta config/db.php");
    if (!file_exists('../../middleware/auth.php')) throw new Exception("Falta middleware/auth.php");

    require_once '../../config/db.php';
    require_once '../../middleware/auth.php';

    // 2. Leer JSON de entrada
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!$data) throw new Exception("No se recibieron datos JSON válidos");

    $id = $data['id'] ?? null;
    $disponible = isset($data['disponible']) ? $data['disponible'] : null;

    if (!$id || $disponible === null) {
        throw new Exception("Faltan datos (ID o Disponibilidad)");
    }

    // 3. Actualizar en BD
    $stmt = $conn->prepare("UPDATE propiedades SET disponible = ? WHERE id = ?");
    $stmt->bind_param("ii", $disponible, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Estado actualizado"]);
    } else {
        throw new Exception("Error SQL: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>