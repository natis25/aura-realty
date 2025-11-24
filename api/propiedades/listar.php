<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

// Parámetros opcionales
$search = $_GET['search'] ?? '';

// Consulta con búsqueda
$sql = "SELECT id, titulo, direccion, ciudad, tipo, precio, area, habitaciones, banos, descripcion, imagen_principal, disponible 
        FROM propiedades 
        WHERE titulo LIKE ? OR ciudad LIKE ? 
        ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();

$propiedades = [];
while($row = $result->fetch_assoc()){
    $propiedades[] = $row;
}

echo json_encode(["success"=>true, "propiedades"=>$propiedades]);
$conn->close();
?>
