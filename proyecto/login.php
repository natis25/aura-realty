<link rel="stylesheet" href="css/styles.css">

<?php
require_once 'config.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Rellena todos los campos.";
    } else {
        $stmt = $conexion->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $nombre, $hash);
            $stmt->fetch();

            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $nombre;
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Contraseña incorrecta.";
            }
        } else {
            $errors[] = "No existe cuenta con ese email.";
        }

        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login de usuarios</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
  <h1>Iniciar sesión</h1>

  <?php if ($errors): foreach ($errors as $e): ?>
    <div class="error"><?=htmlspecialchars($e)?></div>
  <?php endforeach; endif; ?>

  <form method="post" action="login.php">
    <div class="form-row">
      <label>Usuario</label>
      <input type="text" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
    </div>
    <div class="form-row">
      <label>Contraseña</label>
      <input type="password" name="password">
    </div>
    <button class="btn" type="submit">Iniciar Sesión</button>
  </form>
  <p class="signup"><a href="register.php">¿No tienes cuenta? Regístrate aqui</a></p>
</div>
</body>
</html>
