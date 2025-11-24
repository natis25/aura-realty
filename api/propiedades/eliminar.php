<?php
header('Content-Type: application/json');
include_once("../../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if(!$id){
    echo json_encode(["success"=>false,"message"=>"ID de propiedad no recibido"]);
    exit;
}

// Traer imagen para eliminarla físicamente
$res = $conn->query("SELECT imagen_principal FROM propiedades WHERE id=$id");
if($res->num_rows > 0){
    $prop = $res->fetch_assoc();
    if($prop['imagen_principal']){
        $ruta = "../../uploads/propiedades/" . $prop['imagen_principal'];
        if(file_exists($ruta)) unlink($ruta);
    }
}

// Eliminar propiedad
$stmt = $conn->prepare("DELETE FROM propiedades WHERE id=?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Propiedad eliminada correctamente"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Error al eliminar propiedad","error"=>$stmt->error]);
}

$conn->close();
?>
