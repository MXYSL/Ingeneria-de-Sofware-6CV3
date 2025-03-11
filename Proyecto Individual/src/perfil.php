<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, imagen FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $imagen);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center mb-4">Perfil de Usuario</h2>

        <!-- Foto de Perfil -->
        <div class="text-center">
            <img src="<?php echo $imagen ? 'uploads/' . $imagen : 'https://cdn-icons-png.freepik.com/512/10593/10593499.png'; ?>" 
                 class="rounded-circle img-thumbnail" 
                 style="width: 150px; height: 150px; object-fit: cover;" 
                 alt="Foto de perfil">
        </div>

        <!-- Formulario para cambiar imagen -->
        <form action="actualizar_imagen.php" method="POST" enctype="multipart/form-data" class="mt-3">
            <div class="mb-3">
                <input type="file" class="form-control" name="imagen" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar Foto</button>
        </form>

        <!-- Botón para eliminar foto de perfil (Solo si el usuario tiene una imagen) -->
        <?php if ($imagen): ?>
            <form action="eliminar_imagen.php" method="POST" class="mt-2">
                <button type="submit" class="btn btn-danger w-100">Eliminar Foto</button>
            </form>
        <?php endif; ?>

        <hr>

        <!-- Formulario para editar perfil -->
        <form action="actualizar_perfil.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" name="password" placeholder="Dejar en blanco para no cambiar">
            </div>
            <button type="submit" class="btn btn-success w-100">Guardar Cambios</button>
            <a href="home.php" class="menu-button">Regresar</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
