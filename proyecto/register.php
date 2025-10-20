<?php
require_once 'config.php';
session_start();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($password !== $confirmar) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, usuario, email, telefono, direccion, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $nombre, $apellido, $usuario, $email, $telefono, $direccion, $hash);
        
        if ($stmt->execute()) {
            header('Location: login.php');
            exit;
        } else {
            $errores[] = "Error al registrar el usuario.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Registro de Clientes</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body class="register-page">
  <div class="register-container">
    <h1>Registro de Clientes</h1>

    <?php if ($errores): foreach ($errores as $e): ?>
      <div style="color:red; margin-bottom:10px;"><?=htmlspecialchars($e)?></div>
    <?php endforeach; endif; ?>

    <form method="post" class="register-form">
      <div>
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
      </div>
      <div>
        <label>Apellido:</label>
        <input type="text" name="apellido" required>
      </div>
      <div class="full-width">
        <label>Usuario:</label>
        <input type="text" name="usuario" required>
      </div>
      <div>
        <label>Correo electrónico:</label>
        <input type="email" name="email" required>
      </div>
      <div>
        <label>Teléfono:</label>
        <input type="text" name="telefono" required>
      </div>
      <div class="full-width">
        <label>Dirección:</label>
        <input type="text" name="direccion" required>
      </div>
      <div>
        <label>Contraseña:</label>
        <input type="password" name="password" required>
      </div>
      <div>
        <label>Confirmar Contraseña:</label>
        <input type="password" name="confirmar" required>
      </div>
      <button type="submit" class="btn-register">Registrarse</button>
    </form>

    <p class="signup-link">¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
  </div>
</body>
</html>








