<?php
include_once 'config/database.php';
include_once 'models/Propiedad.php';

$database = new Database();
$db = $database->getConnection();
$propiedad = new Propiedad($db);
$stmt = $propiedad->leer();
$num = $stmt->rowCount();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Propiedades</title>
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .acciones a { margin-right: 10px; text-decoration: none; }
        .editar { color: #007bff; }
        .eliminar { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestión de Propiedades</h1>
            <a href="formulario.php" class="btn">Nueva Propiedad</a>
        </div>

        <?php if($num > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Precio</th>
                        <th>Habitaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($row['ciudad']); ?></td>
                            <td>$<?php echo number_format($row['precio'], 2); ?></td>
                            <td><?php echo $row['habitaciones']; ?></td>
                            <td class="acciones">
                                <a href="formulario.php?accion=editar&id=<?php echo $row['id']; ?>" class="editar">Editar</a>
                                <a href="eliminar.php?id=<?php echo $row['id']; ?>" class="eliminar" onclick="return confirm('¿Estás seguro de eliminar esta propiedad?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay propiedades registradas.</p>
        <?php endif; ?>
    </div>
</body>
</html>