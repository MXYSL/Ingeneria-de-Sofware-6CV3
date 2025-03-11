<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener la imagen actual del usuario
$stmt = $conn->prepare("SELECT imagen FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($imagen);
$stmt->fetch();
$stmt->close();

// Si hay una imagen y no es la predeterminada, eliminarla del servidor
if ($imagen) {
    $ruta_imagen = "uploads/" . $imagen;
    if (file_exists($ruta_imagen)) {
        unlink($ruta_imagen);
    }
}

// Actualizar la base de datos para eliminar la referencia de la imagen
$stmt = $conn->prepare("UPDATE users SET imagen=NULL WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Redirigir de nuevo al perfil
header("Location: perfil.php");
exit();
?>
