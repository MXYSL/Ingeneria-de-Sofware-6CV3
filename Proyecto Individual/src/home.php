<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Obtener datos del usuario desde la sesión
$nombreUsuario = $_SESSION['username'];
$isAdmin = ($_SESSION['role_id'] == 1); // Suponiendo que el rol 1 es Admin

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Recomendaciones</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        
        .header {
            background-color: #1a237e;
            color: white;
            width: 100%;
            padding: 1rem 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 3rem;
            width: 80%;
            max-width: 600px;
            text-align: center;
        }
        
        h1 {
            margin-bottom: 0.5rem;
            color: #ffffff;
        }
        .container h1 {
            margin-bottom: 0.5rem;
            color: #000000;
        }
        
        .welcome-message {
            color: #060606;
            margin-bottom: 2rem;
            font-size: 50px;
            font-weight: bold;
        }
        
        .menu-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .menu-button {
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem 2rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .menu-button:hover {
            background-color: #303f9f;
            transform: scale(1.05);
        }
        
        .logout {
            margin-top: 2rem;
            padding: 0.75rem 2rem;
            background: rgba(63, 81, 181, 0.8);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s, transform 0.2s;
        }
        
        .logout:hover {
            background: rgba(48, 63, 159, 1);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema de Recomendaciones</h1>
    </div>
    
    <div class="container">
        <h1>Bienvenido</h1>
        <p class="welcome-message"> "<span style="color: #1a237e; font-size: 55px;"><?php echo htmlspecialchars($nombreUsuario); ?></span>" c:</p>
        
        <div class="menu-options">
            <a href="/libros" class="menu-button">Libros</a>
            <a href="/peliculas" class="menu-button">Películas</a>
            <a href="perfil.php" class="menu-button">Mi Perfil</a> 

            <!-- Botón de Administrador solo si el usuario es Admin -->
            <?php if ($isAdmin): ?>
                <a href="admin.php" class="menu-button">Administrador</a>
            <?php endif; ?>
        </div>
        
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </div>
</body>
</html>
