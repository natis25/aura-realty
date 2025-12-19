<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

// 1. Recibir datos del FormData
$titulo = $_POST['titulo'] ?? null;
$direccion = $_POST['direccion'] ?? null;
$ciudad = $_POST['ciudad'] ?? null;
$tipo = $_POST['tipo'] ?? 'venta';
$precio = $_POST['precio'] ?? 0;
$area = $_POST['area'] ?? 0;
$habitaciones = $_POST['habitaciones'] ?? 0;
$banos = $_POST['banos'] ?? 0;

// IMPORTANTE: Capturar descripción. Si viene vacía, guardar string vacío.
$descripcion = $_POST['descripcion'] ?? ''; 

// Por defecto al crear es 1 (Activa)
$disponible = 1;

if(!$titulo || !$ciudad) {
    echo json_encode(["success"=>false, "message"=>"Título y ciudad son obligatorios"]);
    exit;
}

// 2. Manejo de Imagen
$imagenNombre = null;
if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagenNombre = uniqid() . "." . $ext;
    $destino = "../../uploads/propiedades/" . $imagenNombre;
    move_uploaded_file($_FILES['imagen']['tmp_name'], $destino);
}

// 3. Insertar en DB incluyendo la columna 'descripcion'
$sql = "INSERT INTO propiedades 
    (titulo, direccion, ciudad, tipo, precio, area, habitaciones, banos, descripcion, imagen_principal, disponible)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// s=string, d=double, i=integer
// Orden: titulo(s), direccion(s), ciudad(s), tipo(s), precio(d), area(d), hab(i), banos(i), desc(s), img(s), disp(i)
$stmt->bind_param("ssssddiissi", $titulo, $direccion, $ciudad, $tipo, $precio, $area, $habitaciones, $banos, $descripcion, $imagenNombre, $disponible);

if($stmt->execute()){
    echo json_encode(["success"=>true, "message"=>"Propiedad creada correctamente"]);
} else {
    echo json_encode(["success"=>false, "message"=>"Error SQL: " . $stmt->error]);
}
$conn->close();
?>