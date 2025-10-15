<?php
include_once 'config/database.php';
include_once 'models/Propiedad.php';

$database = new Database();
$db = $database->getConnection();
$propiedad = new Propiedad($db);

if(isset($_GET['id'])) {
    $propiedad->id = $_GET['id'];
    
    if($propiedad->eliminar()) {
        header("Location: index.php?mensaje=Propiedad eliminada exitosamente");
    } else {
        header("Location: index.php?error=Error al eliminar la propiedad");
    }
} else {
    header("Location: index.php");
}
?>