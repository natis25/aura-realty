<?php
// api/citas/listar.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once '../../config/db.php';

// Get the user ID and Role from the request (or token)
// For simplicity, we'll assume the frontend sends the user_id or we decode the token here.
// Let's rely on the frontend sending the user_id for this specific request structure you have.
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
$rol = isset($_GET['rol']) ? $_GET['rol'] : '';

if ($usuario_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Falta ID de usuario']);
    exit;
}

$sql = "";
$params = [];
$types = "";

if ($rol === 'cliente') {
    // JOIN query to get appointment details + property info + agent info
    // We link citas -> solicitudes_cita -> propiedades
    $sql = "SELECT 
                c.id, 
                c.fecha, 
                c.hora, 
                c.estado,
                c.nota,
                p.titulo AS propiedad_titulo,
                p.direccion,
                p.ciudad,
                u_agente.nombre AS agente_nombre
            FROM citas c
            INNER JOIN solicitudes_cita s ON c.solicitud_id = s.id
            INNER JOIN propiedades p ON s.propiedad_id = p.id
            LEFT JOIN agentes a ON c.agente_id = a.id
            LEFT JOIN usuarios u_agente ON a.usuario_id = u_agente.id
            WHERE s.usuario_id = ? 
            ORDER BY c.fecha DESC, c.hora DESC";
    
    $params[] = $usuario_id;
    $types = "i";

} else if ($rol === 'agente') {
    // Logic for agent (seeing their own appointments)
    // You would join with agentes table to verify the agent_id matches the user
     $sql = "SELECT 
                c.id, c.fecha, c.hora, c.estado, c.nota,
                p.titulo AS propiedad_titulo,
                u_cliente.nombre AS cliente_nombre
            FROM citas c
            INNER JOIN solicitudes_cita s ON c.solicitud_id = s.id
            INNER JOIN propiedades p ON s.propiedad_id = p.id
            INNER JOIN usuarios u_cliente ON s.usuario_id = u_cliente.id
            WHERE c.agente_id = (SELECT id FROM agentes WHERE usuario_id = ?)
            ORDER BY c.fecha DESC";
            
    $params[] = $usuario_id;
    $types = "i";
} else {
    // Admin or other
    echo json_encode(['success' => false, 'message' => 'Rol no válido']);
    exit;
}

try {
    $stmt = $conn->prepare($sql);
    if(!$stmt) throw new Exception("Error en consulta: " . $conn->error);
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $citas = [];
    while ($row = $result->fetch_assoc()) {
        $citas[] = $row;
    }

    echo json_encode(['success' => true, 'citas' => $citas]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>