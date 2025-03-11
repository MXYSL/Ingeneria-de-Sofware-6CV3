<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Acceso denegado.");
}

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "spring_auth");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($email)) {
        exit("Todos los campos son obligatorios.");
    }

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $password, $id);
    } else {
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $id);
    }

    $stmt->execute();
    header("Location: admin.php");
}
?>
