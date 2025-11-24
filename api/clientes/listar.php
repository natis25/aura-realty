<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

try {
    // Traer clientes (usuarios con rol_id = 2, suponiendo que 2 = cliente)
    $sql = "SELECT id, nombre FROM usuarios WHERE rol_id = 2";
    $result = $conn->query($sql);

    if (!$result) throw new Exception($conn->error);

    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }

    echo json_encode([
        "success" => true,
        "clientes" => $clientes
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar clientes",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>
