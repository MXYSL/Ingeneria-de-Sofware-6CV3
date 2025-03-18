<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

echo "<h1>Bienvenido al Panel de Usuario</h1>";
echo "<p>Usuario: " . $_SESSION['username'] . "</p>";
echo "<a href='logout.php'>Cerrar sesión</a>";
?>
