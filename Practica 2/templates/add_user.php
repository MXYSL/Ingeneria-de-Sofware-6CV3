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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validar datos
    if (empty($nombre) || empty($email) || empty($password)) {
        http_response_code(400);
        exit("Todos los campos son obligatorios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        exit("Email inválido.");
    }

    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(400);
        exit("El email ya está registrado.");
    }
    $stmt->close();

    // Hash de la contraseña
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar usuario (por defecto como usuario normal: role_id = 2)
    $role_id = 2;
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nombre, $email, $passwordHash, $role_id);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario agregado correctamente.'); window.location.href='admin.php';</script>";
    } else {
        http_response_code(500);
        echo "<script>alert('Error al agregar usuario.'); window.location.href='admin.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
