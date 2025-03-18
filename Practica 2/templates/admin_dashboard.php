<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

echo "<h1>Bienvenido al Panel de Administración</h1>";
echo "<p>Usuario: " . $_SESSION['username'] . "</p>";
echo "<a href='logout.php'>Cerrar sesión</a>";
?>
