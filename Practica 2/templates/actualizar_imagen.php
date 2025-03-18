<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
    $dir = "uploads/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $imagen = basename($_FILES['imagen']['name']);
    $ruta = $dir . $imagen;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
        $stmt = $conn->prepare("UPDATE users SET imagen=? WHERE id=?");
        $stmt->bind_param("si", $imagen, $user_id);
        $stmt->execute();
        header("Location: perfil.php");
    } else {
        echo "Error al subir imagen.";
    }
} else {
    echo "Error en la carga.";
}
?>
