<?php
// Incluir archivos de configuración y modelo
include_once 'config/database.php';
include_once 'models/Property.php';

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear objeto Property
$property = new Property($db);

// Procesar filtros
$filters = [];
if($_POST) {
    if(!empty($_POST['zona'])) {
        $filters['zona'] = $_POST['zona'];
    }
    if(!empty($_POST['tipo_contrato'])) {
        $filters['tipo_contrato'] = $_POST['tipo_contrato'];
    }
    if(!empty($_POST['tipo_vivienda'])) {
        $filters['tipo_vivienda'] = $_POST['tipo_vivienda'];
    }
    if(!empty($_POST['precio_min'])) {
        $filters['precio_min'] = $_POST['precio_min'];
    }
    if(!empty($_POST['precio_max'])) {
        $filters['precio_max'] = $_POST['precio_max'];
    }
}

// Obtener propiedades
$stmt = $property->read($filters);

// Obtener opciones para filtros
$zonas_stmt = $property->getZonas();
$tipos_contrato_stmt = $property->getTiposContrato();
$tipos_vivienda_stmt = $property->getTiposVivienda();
$precio_maximo = $property->getPrecioMaximo();

// Obtener mensaje de éxito si existe
$success_message = "";
if(isset($_GET['message'])) {
    $success_message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Propiedades</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .title {
            color: #D4AF37;
            font-size: 2.5em;
            text-align: center;
            margin: 20px 0;
        }
        .btn-add {
            background-color: #1E3A8A;
            color: #D4AF37;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .filters {
            background-color: #1E3A8A;
            color: #D4AF37;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filter-group input, .filter-group select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .btn-filter {
            background-color: #D4AF37;
            color: #1E3A8A;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            align-self: flex-end;
        }
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .property-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 400px;
        }
        .property-image {
            height: 50%;
            overflow: hidden;
        }
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .property-info {
            padding: 15px;
            height: 50%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .property-name {
            color: #D4AF37;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .property-details {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .property-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        .btn-edit {
            background-color: #28a745;
            color: white;
            text-decoration: none;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .no-properties {
            grid-column: 1 / -1;
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .range-slider {
            width: 200px;
        }
        .range-values {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 0.8em;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">VIVIENDAS</h1>
        <a href="add_property.php" class="btn-add">Añadir Propiedades</a>
    </div>

    <?php if(!empty($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="filters">
        <form method="POST" class="filter-form">
            <div class="filter-group">
                <label for="zona">Zona</label>
                <select name="zona" id="zona">
                    <option value="">Todas las zonas</option>
                    <?php
                    while ($row = $zonas_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (!empty($filters['zona']) && $filters['zona'] == $row['zona']) ? 'selected' : '';
                        echo "<option value='{$row['zona']}' $selected>{$row['zona']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="tipo_contrato">Contrato</label>
                <select name="tipo_contrato" id="tipo_contrato">
                    <option value="">Todos los contratos</option>
                    <?php
                    while ($row = $tipos_contrato_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (!empty($filters['tipo_contrato']) && $filters['tipo_contrato'] == $row['tipo_contrato']) ? 'selected' : '';
                        echo "<option value='{$row['tipo_contrato']}' $selected>{$row['tipo_contrato']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="tipo_vivienda">Tipo</label>
                <select name="tipo_vivienda" id="tipo_vivienda">
                    <option value="">Todos los tipos</option>
                    <?php
                    while ($row = $tipos_vivienda_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (!empty($filters['tipo_vivienda']) && $filters['tipo_vivienda'] == $row['tipo_vivienda']) ? 'selected' : '';
                        echo "<option value='{$row['tipo_vivienda']}' $selected>{$row['tipo_vivienda']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="precio_range">Rango de Precio</label>
                <input type="range" id="precio_range" name="precio_range" 
                       min="0" max="<?php echo $precio_maximo; ?>" 
                       value="<?php echo !empty($filters['precio_max']) ? $filters['precio_max'] : $precio_maximo; ?>"
                       class="range-slider">
                <div class="range-values">
                    <span>$0</span>
                    <span id="precio_value">$<?php echo !empty($filters['precio_max']) ? number_format($filters['precio_max'], 0) : number_format($precio_maximo, 0); ?></span>
                </div>
                <input type="hidden" name="precio_min" value="0">
                <input type="hidden" name="precio_max" id="precio_max" 
                       value="<?php echo !empty($filters['precio_max']) ? $filters['precio_max'] : $precio_maximo; ?>">
            </div>
            
            <button type="submit" class="btn-filter">Filtrar</button>
        </form>
    </div>

    <div class="properties-grid">
        <?php
        if($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<div class='property-card'>";
                echo "<div class='property-image'>";
                echo "<img src='uploads/{$row['imagen']}' alt='{$row['nombre']}'>";
                echo "</div>";
                echo "<div class='property-info'>";
                echo "<div class='property-name'>{$row['nombre']}</div>";
                echo "<div class='property-details'>Tipo: {$row['tipo_vivienda']}</div>";
                echo "<div class='property-details'>Precio: $" . number_format($row['precio'], 2) . "</div>";
                echo "<div class='property-details'>Dirección: {$row['direccion']}</div>";
                echo "<div class='property-actions'>";
                echo "<a href='edit_property.php?id={$row['id']}' class='btn-edit'>Editar</a>";
                echo "<a href='delete_property.php?id={$row['id']}' class='btn-delete' onclick='return confirm(\"¿Está seguro de eliminar esta propiedad?\")'>Eliminar</a>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='no-properties'>No se encontraron propiedades con los filtros seleccionados.</div>";
        }
        ?>
    </div>

    <script>
        // Actualizar el valor del rango de precio
        const precioRange = document.getElementById('precio_range');
        const precioValue = document.getElementById('precio_value');
        const precioMax = document.getElementById('precio_max');

        precioRange.addEventListener('input', function() {
            const value = this.value;
            precioValue.textContent = '$' + Number(value).toLocaleString();
            precioMax.value = value;
        });
    </script>
</body>
</html>