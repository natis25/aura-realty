<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once("../../config/db.php");

error_reporting(E_ALL);
ini_set("display_errors", 1);

try {

    // =========================
    // 1. Número de agentes
    // =========================
    $res = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol_id = 3");
    $agentes = $res ? intval($res->fetch_assoc()['total']) : 0;

    // =========================
    // 2. Propiedades activas
    // (tu BD usa 'disponible', no 'estado')
    // =========================
    $res = $conn->query("SELECT COUNT(*) AS total FROM propiedades WHERE disponible = 1");
    $propiedades_activas = $res ? intval($res->fetch_assoc()['total']) : 0;

    // =========================
    // 3. Propiedades vendidas
    // (tu tabla NO tiene 'estado', así que este valor será 0
    //  puedes crear el campo si lo necesitas)
    // =========================
    $propiedades_vendidas = 0;

    // =========================
    // 4. Citas hoy
    // =========================
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM solicitudes_cita WHERE fecha_solicitada=?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $res = $stmt->get_result();
    $citas_hoy = $res ? intval($res->fetch_assoc()['total']) : 0;

    // =========================
    // 5. Citas últimos 7 días
    // =========================
    $labels = [];
    $values = [];
    for ($i=6; $i>=0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));

        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM solicitudes_cita WHERE fecha_solicitada=?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $res = $stmt->get_result();
        $count = $res ? intval($res->fetch_assoc()['total']) : 0;

        $labels[] = date('d M', strtotime($date));
        $values[] = $count;
    }

    // =========================
    // 6. Top 5 agentes con más citas
    // =========================
    $sql = "
        SELECT u.nombre, COUNT(s.id) AS citas
        FROM usuarios u
        LEFT JOIN agentes a ON a.usuario_id = u.id
        LEFT JOIN solicitudes_cita s ON a.id = s.agente_asignado
        WHERE u.rol_id = 3
        GROUP BY u.id
        ORDER BY citas DESC
        LIMIT 5
    ";

    $res = $conn->query($sql);
    $top_agentes = [];
    
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $top_agentes[] = $row;
        }
    }

    // =========================
    // RESPUESTA JSON
    // =========================
    echo json_encode([
        "success" => true,
        "agentes" => $agentes,
        "propiedades_activas" => $propiedades_activas,
        "propiedades_vendidas" => $propiedades_vendidas,
        "citas_hoy" => $citas_hoy,
        "citas_7dias" => [
            "labels" => $labels,
            "values" => $values
        ],
        "top_agentes" => $top_agentes
    ]);

} catch(Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al cargar métricas",
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>
