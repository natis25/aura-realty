<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

// 1. Validar ID
$id = $_POST['id'] ?? null;
if(!$id){
    echo json_encode(["success"=>false, "message"=>"Falta ID para editar"]);
    exit;
}

// 2. Recibir datos del FormData
$titulo = $_POST['titulo'] ?? null;
$direccion = $_POST['direccion'] ?? null;
$ciudad = $_POST['ciudad'] ?? null;
$tipo = $_POST['tipo'] ?? 'venta';
$precio = $_POST['precio'] ?? 0;
$area = $_POST['area'] ?? 0;
$habitaciones = $_POST['habitaciones'] ?? 0;
$banos = $_POST['banos'] ?? 0;
$descripcion = $_POST['descripcion'] ?? ''; // Capturar descripción
$disponible = $_POST['disponible'] ?? 1;    // Capturar disponibilidad del select

// 3. Manejo de Imagen (Solo si se sube una nueva)
$imagenSQL = ""; 
$types = "ssssddiisi"; // Tipos base sin imagen: tit, dir, ciu, tip, pre, are, hab, ban, desc, disp
$params = [$titulo, $direccion, $ciudad, $tipo, $precio, $area, $habitaciones, $banos, $descripcion, $disponible];

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagenNombre = uniqid() . "." . $ext;
    $destino = "../../uploads/propiedades/" . $imagenNombre;
    if(move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)){
        // Si hay nueva imagen, agregamos al SQL
        $imagenSQL = ", imagen_principal=?";
        $types .= "s"; // Agregamos tipo string para la imagen
        $params[] = $imagenNombre;
    }
}

// Agregamos el ID al final para el WHERE
$types .= "i"; 
$params[] = $id;

// 4. Sentencia UPDATE
// Nota: Aquí agregamos 'descripcion=?'
$sql = "UPDATE propiedades SET 
        titulo=?, direccion=?, ciudad=?, tipo=?, precio=?, area=?, habitaciones=?, banos=?, descripcion=?, disponible=? $imagenSQL 
        WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if($stmt->execute()){
    echo json_encode(["success"=>true, "message"=>"Propiedad actualizada correctamente"]);
} else {
    echo json_encode(["success"=>false, "message"=>"Error al editar: " . $stmt->error]);
}
$conn->close();
?>