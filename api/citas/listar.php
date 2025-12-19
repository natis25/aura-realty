<?php
header("Content-Type: application/json");
require_once "../../config/db.php";
// Comenta temporalmente la validación para probar si es el Token
// require_once "../../middleware/auth.php"; 
// $user = validate_jwt(); 

$search = isset($_GET['search']) ? $_GET['search'] : '';
$term = "%$search%";

// Consulta con LEFT JOIN para asegurar que nada se filtre por error
$sql = "SELECT 
            s.id, 
            s.fecha_solicitada AS fecha, 
            s.hora_solicitada AS hora, 
            s.estado, 
            u.nombre AS cliente, 
            p.titulo AS propiedad, 
            ua.nombre AS agente_nombre
        FROM solicitudes_cita s
        LEFT JOIN usuarios u ON s.usuario_id = u.id
        LEFT JOIN propiedades p ON s.propiedad_id = p.id
        LEFT JOIN agentes ag ON s.agente_asignado = ag.id
        LEFT JOIN usuarios ua ON ag.usuario_id = ua.id
        WHERE (u.nombre LIKE ? OR p.titulo LIKE ?)
        ORDER BY s.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $term, $term);
$stmt->execute();
$result = $stmt->get_result();

$citas = [];
while($row = $result->fetch_assoc()) {
    $citas[] = $row;
}

// Verifica que esto imprima algo en el navegador si entras directo a la URL
echo json_encode(["success" => true, "citas" => $citas]);