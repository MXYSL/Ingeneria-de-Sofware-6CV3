<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once 'conexion.php'; // Incluye la conexión a la base de datos

$email = $_SESSION['user_id']; // Email como identificador
$username = $_SESSION['username'];
$role_id = $_SESSION['role_id'];

// Obtener datos completos del usuario
$stmt = $conn->prepare("SELECT username, email, imagen, role_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $email = $user['email'];
    $imagen = $user['imagen'];
    $role_id = $user['role_id'];
} else {
    echo "Error: usuario no encontrado.";
    exit();
}

// Opcional: Convertir ID de rol a nombre
$roles = [
    1 => "Administrador",
    2 => "Usuario"
];
$rol_nombre = $roles[$role_id] ?? "Desconocido";

// Procesar actualización de imagen
if (isset($_POST['action']) && $_POST['action'] == 'update_image' && isset($_FILES['imagen'])) {
    if ($_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $dir = "uploads/";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $imagen_nombre = basename($_FILES['imagen']['name']);
        $ruta = $dir . $imagen_nombre;

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['imagen']['tmp_name'];
            $file_name = $_FILES['imagen']['name'];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
            // Validar extensión
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_ext), $allowed_ext)) {
                $new_file_name = uniqid('img_') . "." . $file_ext;
                $target_path = "uploads/" . $new_file_name;
        
                if (move_uploaded_file($file_tmp, $target_path)) {
                    // Guardar ruta en la base de datos
                    $stmt = $conn->prepare("UPDATE users SET imagen = ? WHERE email = ?");
                    $stmt->bind_param("ss", $target_path, $email);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo "Error al subir la imagen.";
                }
            } else {
                echo "Tipo de archivo no permitido.";
            }
        }
        
    } else {
        $error_msg = "Error en la carga de la imagen.";
    }
}

// Procesar eliminación de imagen
if (isset($_POST['action']) && $_POST['action'] == 'delete_image') {
    echo "Action: delete_image - Imagen: " . htmlspecialchars($imagen); // Depurar imagen
    if ($imagen) {
        $ruta_imagen = "uploads/" . $imagen;
        if (file_exists($ruta_imagen)) {
            if (unlink($ruta_imagen)) {
                echo "Imagen eliminada del servidor."; // Confirmación si se eliminó
            } else {
                echo " "; // Si no se puede eliminar
            }
        } else {
            echo " "; // Si no existe el archivo
        }

        // Eliminar imagen de la base de datos
        $stmt = $conn->prepare("UPDATE users SET imagen=NULL WHERE email=?"); // Cambié a email en vez de id, si no estás usando id
        $stmt->bind_param("s", $email); // Cambié a email para ser consistente con el resto del código
        if ($stmt->execute()) {
            echo "Imagen eliminada de la base de datos."; // Verifica si se actualiza la base de datos
        } else {
            echo "Error al eliminar imagen de la base de datos."; // Si hay un error al eliminar
        }
        $stmt->close();

        // Actualizar la variable de imagen para reflejar el cambio
        $imagen = null;
    }
}

// Procesar actualización de perfil
if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $new_username, $email, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    }

    if ($stmt->execute()) {
        // Actualizar las variables para mostrar los datos actualizados
        $username = $new_username;
        $email = $new_email;
        $success_msg = "Perfil actualizado correctamente";
    } else {
        $error_msg = "Error al actualizar el perfil.";
    }
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="img/icono.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Playfair+Display:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="perfil.css">
    <title>Mi Perfil</title>
</head>
<body>
    <div class="app-container14">
        <!-- Botón de menú hamburguesa para móvil -->
        <button class="menu-toggle14" id="menuToggle14">
        <i class="fa-duotone fa-solid fa-ellipsis"></i>
        </button>

        <!-- Sidebar -->
        <div class="sidebar14" id="sidebar14">
            <div class="logo14">
            <i class="fa-brands fa-audible"></i><span>StoryVerse</span>
            </div>
            <a href="instorve.php" class="menu-item14"><i class="fas fa-house-user"></i> Inicio </a>
            <a href="#" class="menu-item14 active14"><i class="fas fa-user"></i> Perfil </a>
            <a href="instorve.php" id="favorites-link" class="menu-item14"><i class="fa-solid fa-heart"></i> Favoritos </a>
            <a href="auth.php?action=logout" class="menu-item14"><i class="fa-solid fa-right-to-bracket"></i>  Cerrar Sesión</a>
        </div>

        <!-- Contenido principal -->
        <div class="main-content14">
            <div class="header14">
                <div class="header-actions14">
                    <button class="theme-toggle14" id="themeToggle14">
                        <i id="sunIcon14" class="fa-regular fa-sun" fill="none" stroke="currentColor" stroke-width="2" style="display: none;" stroke-linecap="round" stroke-linejoin="round"></i>
                        <i id="moonIcon14" class="fa-regular fa-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></i>
                    </button>
                    <img id= "icono" class="user-avatar14" src="img/icono.png" alt="StoryVerse">
                </div>
            </div>

            <div class="profile-container14">
                <!-- Mensajes de alerta -->
                <?php if (isset($success_msg)): ?>
                    <div class="alert alert-success" style="width: 100%;"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_msg)): ?>
                    <div class="alert alert-danger" style="width: 100%;"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <div class="profile-sidebar14">
                <img class="profile-image14" src="<?php echo $imagen ? 'uploads/' . htmlspecialchars($imagen) : 'https://cdn-icons-png.freepik.com/512/10593/10593499.png'; ?>" alt="Profile Image">
                <h3 class="profile-name14"><?php echo htmlspecialchars($username); ?></h3>
                    <p class="profile-title14"><?php echo htmlspecialchars($rol_nombre ?? 'Usuario'); ?></p>
                    
                    <div class="profile-stats14">
                        <div class="stat-item14">
                            <span class="stat-value14">254</span>
                            <span class="stat-label14">Followers</span>
                        </div>
                        <div class="stat-item14">
                            <span class="stat-value14">54</span>
                            <span class="stat-label14">Following</span>
                        </div>
                    </div>
                    
                    <!-- Formulario para cambiar imagen -->
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_image">
                        <div class="file-input-container">
                            <input type="file" name="imagen" accept="image/*" id="file-input" class="file-input" onchange="this.form.submit()">
                            <button type="button" class="btn-primary" onclick="document.getElementById('file-input').click()">Cambiar foto</button>
                        </div>
                    </form>
                    
                    <!-- Formulario para eliminar imagen -->
                    <?php if ($imagen): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="delete_image">
                        <button type="submit" class="btn-danger">Eliminar foto</button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="profile-main14">
                    <!-- Formulario para actualizar perfil -->
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group14">
                            <label class="form-label14">Usuario</label>
                            <input type="text" class="form-input14" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>

                        <div class="form-group14">
                            <label class="form-label14">Email</label>
                            <input type="email" class="form-input14" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group14">
                            <label class="form-label14">Contraseña</label>
                            <div class="password-container" style="position: relative;">
                                <input type="password" id="password" class="form-input14" name="password" placeholder="Dejar en blanco para no cambiar" style="padding-right: 40px;">
                                <i id="toggle-icon" class="fa-regular fa-eye" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #555;"></i>
                            </div>
                        </div> 
                        <button type="submit" class="upgrade-btn14">Guardar Cambios</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("toggle-icon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
    <script>
        // Toggle menu para móvil
        document.getElementById('menuToggle14').addEventListener('click', function() {
            document.getElementById('sidebar14').classList.toggle('active14');
        });

        // Cambio de tema claro/oscuro
        document.getElementById('themeToggle14').addEventListener('click', function() {
            const htmlElement = document.documentElement;
            const currentTheme = htmlElement.getAttribute('data-theme');
            const moonIcon = document.getElementById('moonIcon14');
            const sunIcon = document.getElementById('sunIcon14');
            const themeicono = document.getElementById("icono");

            
            if (currentTheme === 'dark') {
                htmlElement.removeAttribute('data-theme');
                moonIcon.style.display = 'block';
                sunIcon.style.display = 'none';
                themeicono.src = "img/icono.png"; // Cambiar logo a oscuro
            } else {
                htmlElement.setAttribute('data-theme', 'dark');
                moonIcon.style.display = 'none';
                sunIcon.style.display = 'block';
                themeicono.src = "img/iconoc.png"; // Cambiar logo a claro
            }
        });

        // Cerrar menú al hacer clic en elemento del menú (para móvil)
        const menuItems = document.querySelectorAll('.menu-item14');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar14').classList.remove('active14');
                }
            });
        });

        // Ocultar mensajes de alerta después de 3 segundos
        const alertElements = document.querySelectorAll('.alert');
        if (alertElements.length > 0) {
            setTimeout(function() {
                alertElements.forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 3000);
        }

        // Detectar preferencia de tema del sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: none)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.getElementById('moonIcon14').style.display = 'none';
            document.getElementById('sunIcon14').style.display = 'block';
        }
    </script>
</body>
</html>
