<?php
session_start();

// Si no hay usuario en sesión, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard</title>
</head>
<body>
<h1>Hola <?=htmlspecialchars($_SESSION['user_name'])?></h1>
<p>Bienvenido a tu dashboard.</p>
<p><a href="logout.php">Cerrar sesión</a></p>
</body>
</html>