<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once("../../config/db.php");

try {
    // Traer agentes disponibles junto con el nombre del usuario
    $sql = "
        SELECT a.id, u.nombre
        FROM agentes a
        INNER JOIN usuarios u ON a.usuario_id = u.id
        WHERE a.disponible = 1
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("SQL Error: " . $conn->error);
    }

    $agentes = [];
    while ($row = $result->fetch_assoc()) {
        $agentes[] = $row;
    }

    echo json_encode([
        "success" => true,
        "agentes" => $agentes
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar agentes",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>
