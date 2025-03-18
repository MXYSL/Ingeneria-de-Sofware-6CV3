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
    $password = trim($_POST['password']);

    // Buscar usuario en la base de datos
    $stmt = $conn->prepare("SELECT id, username, password, role_id FROM users WHERE BINARY username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $db_password, $role_id);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['role_id'] = $role_id;
            
            echo "<script>alert('Inicio de sesión exitoso.'); window.location.href='home.php';</script>";
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Usuario no encontrado.'); window.history.back();</script>";
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>
