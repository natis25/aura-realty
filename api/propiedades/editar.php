<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

// Recibir ID
$id = $_POST['id'] ?? null;
if(!$id){
    echo json_encode(["success"=>false,"message"=>"ID de propiedad no recibido"]);
    exit;
}

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
if(!$titulo || !$ciudad){
    echo json_encode(["success"=>false,"message"=>"Faltan datos obligatorios"]);
    exit;
}

// Traer la propiedad actual
$res = $conn->query("SELECT imagen_principal FROM propiedades WHERE id = $id");
if($res->num_rows == 0){
    echo json_encode(["success"=>false,"message"=>"Propiedad no encontrada"]);
    exit;
}
$prop = $res->fetch_assoc();
$imagenNombre = $prop['imagen_principal'];

// Si subieron nueva imagen
if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagenNombre = uniqid() . "." . $ext;
    $destino = "../../uploads/propiedades/" . $imagenNombre;
    if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)){
        echo json_encode(["success"=>false,"message"=>"Error al subir la imagen"]);
        exit;
    }
}

// Actualizar en DB
$stmt = $conn->prepare("UPDATE propiedades SET 
    titulo=?, direccion=?, ciudad=?, tipo=?, precio=?, area=?, habitaciones=?, banos=?, descripcion=?, imagen_principal=?, disponible=? WHERE id=?");
$stmt->bind_param("ssssdiiiisii", $titulo, $direccion, $ciudad, $tipo, $precio, $area, $habitaciones, $banos, $descripcion, $imagenNombre, $disponible, $id);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Propiedad actualizada correctamente"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Error al actualizar propiedad", "error"=>$stmt->error]);
}

$conn->close();
?>
