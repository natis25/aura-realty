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

// Establecer ID de propiedad a editar
$property->id = $_GET['id'];

// Leer los detalles de la propiedad
if(!$property->readOne()) {
    header("Location: index.php");
    exit();
}

// Inicializar variables
$nombre = $property->nombre;
$precio = $property->precio;
$zona = $property->zona;
$tipo_vivienda = $property->tipo_vivienda;
$tipo_contrato = $property->tipo_contrato;
$direccion = $property->direccion;
$imagen_actual = $property->imagen;
$message = "";

// Procesar formulario cuando se envía
if($_POST){
    // Establecer valores de propiedad
    $property->nombre = $_POST['nombre'];
    $property->precio = $_POST['precio'];
    $property->zona = $_POST['zona'];
    $property->tipo_vivienda = $_POST['tipo_vivienda'];
    $property->tipo_contrato = $_POST['tipo_contrato'];
    $property->direccion = $_POST['direccion'];
    
    // Procesar nueva imagen si se subió
    if(!empty($_FILES["imagen"]["name"])) {
        $target_dir = "uploads/";
        
        // Generar nombre único para el archivo
        $original_name = basename($_FILES["imagen"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        // Verificar si es una imagen real
        $check = getimagesize($_FILES["imagen"]["tmp_name"]);
        if($check !== false) {
            // Verificar tamaño del archivo (máximo 5MB)
            if ($_FILES["imagen"]["size"] > 5000000) {
                $message = "Lo sentimos, el archivo es demasiado grande. Máximo 5MB permitido.";
            } else {
                // Permitir ciertos formatos de archivo
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                    $message = "Lo sentimos, solo se permiten archivos JPG, JPEG, PNG y GIF.";
                } else {
                    // Intentar subir el archivo
                    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                        $property->imagen = $new_filename;
                        // Eliminar imagen anterior si existe
                        if(!empty($imagen_actual) && file_exists("uploads/" . $imagen_actual)) {
                            unlink("uploads/" . $imagen_actual);
                        }
                    } else {
                        $message = "Lo sentimos, hubo un error al subir tu archivo.";
                    }
                }
            }
        } else {
            $message = "El archivo no es una imagen válida.";
        }
    } else {
        // Mantener la imagen actual si no se subió una nueva
        $property->imagen = $imagen_actual;
    }
    
    // Actualizar la propiedad si no hay errores
    if(empty($message)) {
        $result = $property->update();
        
        if($result === true){
            header("Location: index.php?message=Propiedad actualizada exitosamente");
            exit();
        } elseif($result === "duplicate") {
            $message = "Error: Ya existe una propiedad con el mismo nombre o dirección.";
            // Eliminar la nueva imagen subida si hubo error de duplicado
            if(isset($new_filename) && file_exists("uploads/" . $new_filename)) {
                unlink("uploads/" . $new_filename);
            }
        } else {
            $message = "No se pudo actualizar la propiedad.";
            // Eliminar la nueva imagen subida si hubo error
            if(isset($new_filename) && file_exists("uploads/" . $new_filename)) {
                unlink("uploads/" . $new_filename);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Propiedad</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('imagen.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 600px;
        }
        .title {
            color: #D4AF37;
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-submit {
            background-color: #1E3A8A;
            color: #D4AF37;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            display: block;
            margin: 30px auto 0;
        }
        .btn-submit:hover {
            background-color: #152C6B;
        }
        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #1E3A8A;
            text-decoration: none;
        }
        .current-image {
            text-align: center;
            margin: 10px 0;
        }
        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Editar Propiedad</h1>
        
        <?php
        if(!empty($message)) {
            $messageClass = (strpos($message, 'exitosamente') !== false) ? 'success' : 'error';
            echo "<div class='message $messageClass'>$message</div>";
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]). "?id=" . $property->id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $precio; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="zona">Zona</label>
                    <input type="text" id="zona" name="zona" value="<?php echo $zona; ?>" required>
                </div>
                <div class="form-group">
                    <label for="tipo_vivienda">Tipo de Vivienda</label>
                    <select id="tipo_vivienda" name="tipo_vivienda" required>
                        <option value="">Seleccione...</option>
                        <option value="Casa" <?php echo ($tipo_vivienda == 'Casa') ? 'selected' : ''; ?>>Casa</option>
                        <option value="Departamento" <?php echo ($tipo_vivienda == 'Departamento') ? 'selected' : ''; ?>>Departamento</option>
                        <option value="PH" <?php echo ($tipo_vivienda == 'PH') ? 'selected' : ''; ?>>PH</option>
                        <option value="Duplex" <?php echo ($tipo_vivienda == 'Duplex') ? 'selected' : ''; ?>>Duplex</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="tipo_contrato">Tipo de Contrato</label>
                <select id="tipo_contrato" name="tipo_contrato" required>
                    <option value="">Seleccione...</option>
                    <option value="Venta" <?php echo ($tipo_contrato == 'Venta') ? 'selected' : ''; ?>>Venta</option>
                    <option value="Alquiler" <?php echo ($tipo_contrato == 'Alquiler') ? 'selected' : ''; ?>>Alquiler</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo $direccion; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="imagen">Nueva Imagen de la Propiedad (opcional)</label>
                <input type="file" id="imagen" name="imagen" accept="image/*">
                <?php if(!empty($imagen_actual)): ?>
                    <div class="current-image">
                        <p>Imagen actual:</p>
                        <img src="uploads/<?php echo $imagen_actual; ?>" alt="<?php echo $nombre; ?>">
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-submit">Actualizar</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">← Volver al listado de propiedades</a>
        </div>
    </div>
</body>
</html>