<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    header("Location: perfil.php");
} else {
    echo "Error al actualizar.";
}
?>
