<?php
include_once 'config/database.php';
include_once 'models/Propiedad.php';

$database = new Database();
$db = $database->getConnection();
$propiedad = new Propiedad($db);

$mensaje = "";
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'crear';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if($accion == 'editar' && $id) {
    $propiedad->id = $id;
    $propiedad->leerUno();
}

if($_POST) {
    $propiedad->titulo = $_POST['titulo'];
    $propiedad->descripcion = $_POST['descripcion'];
    $propiedad->tipo = $_POST['tipo'];
    $propiedad->direccion = $_POST['direccion'];
    $propiedad->ciudad = $_POST['ciudad'];
    $propiedad->precio = $_POST['precio'];
    $propiedad->habitaciones = $_POST['habitaciones'];
    $propiedad->banos = $_POST['banos'];
    $propiedad->metros_cuadrados = $_POST['metros_cuadrados'];

    if($accion == 'crear') {
        if($propiedad->crear()) {
            $mensaje = "Propiedad creada exitosamente.";
            // Limpiar campos después de crear
            $propiedad = new Propiedad($db);
        } else {
            $mensaje = "Error al crear la propiedad.";
        }
    } else {
        $propiedad->id = $id;
        if($propiedad->actualizar()) {
            $mensaje = "Propiedad actualizada exitosamente.";
        } else {
            $mensaje = "Error al actualizar la propiedad.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $accion == 'crear' ? 'Crear' : 'Editar'; ?> Propiedad</title>
    <style>
        .container { max-width: 600px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .mensaje { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $accion == 'crear' ? 'Crear' : 'Editar'; ?> Propiedad</h1>
        
        <?php if($mensaje): ?>
            <div class="mensaje <?php echo strpos($mensaje, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Título:</label>
                <input type="text" name="titulo" value="<?php echo $propiedad->titulo; ?>" required>
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion" rows="4"><?php echo $propiedad->descripcion; ?></textarea>
            </div>

            <div class="form-group">
                <label>Tipo:</label>
                <select name="tipo" required>
                    <option value="casa" <?php echo $propiedad->tipo == 'casa' ? 'selected' : ''; ?>>Casa</option>
                    <option value="departamento" <?php echo $propiedad->tipo == 'departamento' ? 'selected' : ''; ?>>Departamento</option>
                    <option value="local" <?php echo $propiedad->tipo == 'local' ? 'selected' : ''; ?>>Local</option>
                    <option value="terreno" <?php echo $propiedad->tipo == 'terreno' ? 'selected' : ''; ?>>Terreno</option>
                </select>
            </div>

            <div class="form-group">
                <label>Dirección:</label>
                <input type="text" name="direccion" value="<?php echo $propiedad->direccion; ?>" required>
            </div>

            <div class="form-group">
                <label>Ciudad:</label>
                <input type="text" name="ciudad" value="<?php echo $propiedad->ciudad; ?>" required>
            </div>

            <div class="form-group">
                <label>Precio:</label>
                <input type="number" step="0.01" name="precio" value="<?php echo $propiedad->precio; ?>" required>
            </div>

            <div class="form-group">
                <label>Habitaciones:</label>
                <input type="number" name="habitaciones" value="<?php echo $propiedad->habitaciones; ?>">
            </div>

            <div class="form-group">
                <label>Baños:</label>
                <input type="number" name="banos" value="<?php echo $propiedad->banos; ?>">
            </div>

            <div class="form-group">
                <label>Metros Cuadrados:</label>
                <input type="number" name="metros_cuadrados" value="<?php echo $propiedad->metros_cuadrados; ?>">
            </div>

            <button type="submit"><?php echo $accion == 'crear' ? 'Crear' : 'Actualizar'; ?> Propiedad</button>
            <a href="index.php" style="margin-left: 10px;">Volver al listado</a>
        </form>
    </div>
</body>
</html>