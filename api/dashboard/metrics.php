<?php
header("Content-Type: application/json");
require_once "../../config/db.php";

// 1. Total de Visitas y Canceladas
$resVisitas = $conn->query("SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas 
    FROM solicitudes_cita");
$visitas = $resVisitas->fetch_assoc();

// 2. Visitas que llegaron a contrato (estado 'completada' en solicitudes o citas)
$resContratos = $conn->query("SELECT COUNT(*) as total FROM solicitudes_cita WHERE estado = 'completada'");
$contratos = $resContratos->fetch_assoc();

// 3. Propiedades por Tipo de Contrato (Venta, Alquiler)
$resTipoContrato = $conn->query("SELECT tipo, COUNT(*) as cantidad FROM propiedades GROUP BY tipo");
$tiposContrato = [];
while($row = $resTipoContrato->fetch_assoc()) $tiposContrato[] = $row;

// 4. Visitas asignadas por trabajador (Agente)
$resPorAgente = $conn->query("SELECT u.nombre, COUNT(s.id) as total 
    FROM usuarios u 
    JOIN agentes a ON u.id = a.usuario_id 
    LEFT JOIN solicitudes_cita s ON a.id = s.agente_asignado 
    GROUP BY u.id");
$agentes = [];
while($row = $resPorAgente->fetch_assoc()) $agentes[] = $row;

echo json_encode([
    "stats" => [
        "visitas_agendadas" => $visitas['total'],
        "visitas_canceladas" => $visitas['canceladas'],
        "porcentaje_cancelacion" => ($visitas['total'] > 0) ? round(($visitas['canceladas'] / $visitas['total']) * 100, 2) : 0,
        "contratos_exitosos" => $contratos['total']
    ],
    "graficos" => [
        "por_contrato" => $tiposContrato,
        "por_agente" => $agentes
    ]
]);