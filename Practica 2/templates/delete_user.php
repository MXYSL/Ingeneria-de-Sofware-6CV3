<?php
session_start();

// Verifica si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403); // Prohibido
    exit("Acceso denegado.");
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "spring_auth");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // No permitir que el admin se elimine a sí mismo
    if ($id == $_SESSION['user_id']) {
        http_response_code(400);
        exit("No puedes eliminar tu propia cuenta.");
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Usuario eliminado correctamente.";
    } else {
        http_response_code(500);
        echo "Error al eliminar usuario.";
    }

    $stmt->close();
}

$conn->close();
?>
