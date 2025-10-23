<?php
// Incluir archivos de configuración y modelo
include_once 'config/database.php';
include_once 'models/Property.php';

// Verificar si se proporcionó el ID
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear objeto Property
$property = new Property($db);

// Establecer ID de propiedad a eliminar
$property->id = $_GET['id'];

// Leer la propiedad para obtener información de la imagen
if($property->readOne()) {
    $imagen = $property->imagen;
    
    // Eliminar la propiedad
    if($property->delete()) {
        // Eliminar la imagen del servidor si existe
        if(!empty($imagen) && file_exists("uploads/" . $imagen)) {
            unlink("uploads/" . $imagen);
        }
        header("Location: index.php?message=Propiedad eliminada exitosamente");
    } else {
        header("Location: index.php?message=No se pudo eliminar la propiedad");
    }
} else {
    header("Location: index.php?message=Propiedad no encontrada");
}
exit();
?>