<?php 
session_start();

// Conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia esto si tienes otro usuario de MySQL
$password = ""; // Cambia esto si tienes una contraseña
$dbname = "spring_auth";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encripta la contraseña
    $role_id = 2; // Por defecto, asigna el rol de 'USER'

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "<script>alert('El usuario o el correo ya están registrados.'); window.history.back();</script>";
        exit();
    }
    $stmt->close();

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $password, $email, $role_id);

    if ($stmt->execute()) {
        echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión.'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Error al registrar usuario.'); window.history.back();</script>";
    }
    
    $stmt->close();
}

$conn->close();
?>
