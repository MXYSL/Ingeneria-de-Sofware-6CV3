<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403);
    exit("Acceso denegado.");
}

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "spring_auth");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // No permitir que el admin se degrade a sí mismo
    if ($id == $_SESSION['user_id']) {
        http_response_code(400);
        exit("<script>alert('No puedes quitar tu propio rol.'); window.location.href='admin.php';</script>");
    }

    // Obtener el rol actual del usuario
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    $stmt->close();

    if ($role_id === null) {
        http_response_code(404);
        exit("<script>alert('Usuario no encontrado.'); window.location.href='admin.php';</script>");
    }

    // Cambiar el rol
    $nuevoRol = ($role_id == 1) ? 2 : 1; // Si es admin (1), lo baja a usuario (2) y viceversa
    $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevoRol, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Rol actualizado correctamente.'); window.location.href='admin.php';</script>";
    } else {
        http_response_code(500);
        echo "<script>alert('Error al actualizar el rol.'); window.location.href='admin.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
