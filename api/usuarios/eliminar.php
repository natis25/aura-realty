<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
require_once("../../config/db.php");

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id'] ?? 0);

    if ($id === 0) {
        throw new Exception("ID de usuario inválido");
    }

    $sql = "DELETE FROM usuarios WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success"=>true,"message"=>"Usuario eliminado"]);
    } else {
        throw new Exception("Error al eliminar usuario: " . $conn->error);
    }

} catch(Exception $e) {
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
