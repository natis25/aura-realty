<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

// Datos del POST
$titulo = $_POST['titulo'] ?? null;
$direccion = $_POST['direccion'] ?? null;
$ciudad = $_POST['ciudad'] ?? null;
$tipo = $_POST['tipo'] ?? 'venta';
$precio = $_POST['precio'] ?? 0;
$area = $_POST['area'] ?? 0;
$habitaciones = $_POST['habitaciones'] ?? 0;
$banos = $_POST['banos'] ?? 0;
$descripcion = $_POST['descripcion'] ?? '';
$disponible = isset($_POST['disponible']) ? 1 : 0;

// Validar campos obligatorios
if(!$titulo || !$ciudad) {
    echo json_encode(["success"=>false,"message"=>"Faltan datos obligatorios"]);
    exit;
}

// Manejo de imagen
$imagenNombre = null;
if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagenNombre = uniqid() . "." . $ext;
    $destino = "../../uploads/propiedades/" . $imagenNombre;
    if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)){
        echo json_encode(["success"=>false,"message"=>"Error al subir la imagen"]);
        exit;
    }
}

// Insertar en DB
$sql = "INSERT INTO propiedades 
    (titulo, direccion, ciudad, tipo, precio, area, habitaciones, banos, descripcion, imagen_principal, disponible)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssdiiiisi", $titulo, $direccion, $ciudad, $tipo, $precio, $area, $habitaciones, $banos, $descripcion, $imagenNombre, $disponible);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Propiedad creada correctamente"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Error al crear propiedad", "error"=>$stmt->error]);
}

$conn->close();
?>
