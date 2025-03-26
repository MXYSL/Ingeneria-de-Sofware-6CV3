<?php 
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';


// Manejar registro de usuarios
function registrarUsuario($conn, $username, $email, $password) {
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $role_id = 2; // Por defecto, rol de 'USER'

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        return "usuario_existente";
    }
    $stmt->close();

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $password_hash, $email, $role_id);

    if ($stmt->execute()) {
        return "registro_exitoso";
    } else {
        return "error_registro";
    }
}

// Manejar inicio de sesión
function iniciarSesion($conn, $username, $password) {
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
            return "inicio_sesion_exitoso";
        } else {
            return "contrasena_incorrecta";
        }
    } else {
        return "usuario_no_encontrado";
    }
}

// Procesar solicitudes POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determinar si es registro o inicio de sesión
    if (isset($_POST['action'])) {
        $username = trim($_POST['username']);
        
        if ($_POST['action'] == 'registro') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            $resultado = registrarUsuario($conn, $username, $email, $password);
            
            switch ($resultado) {
                case "usuario_existente":
                    echo "<script>alert('El usuario o el correo ya están registrados.'); window.location.href='registro.html';</script>";
                    break;
                case "registro_exitoso":
                    echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión.'); window.location.href='login.html';</script>";
                    break;
                case "error_registro":
                    echo "<script>alert('Error al registrar usuario.'); window.location.href='registro.html';</script>";
                    break;
            }
        } 
        elseif ($_POST['action'] == 'login') {
            $password = trim($_POST['password']);
            
            $resultado = iniciarSesion($conn, $username, $password);
            
            switch ($resultado) {
                case "inicio_sesion_exitoso":
                    echo "<script>alert('Inicio de sesión exitoso.'); window.location.href='instorve.php';</script>";
                    break;
                case "contrasena_incorrecta":
                    echo "<script>alert('Contraseña incorrecta.'); window.history.back();</script>";
                    break;
                case "usuario_no_encontrado":
                    echo "<script>alert('Usuario no encontrado.'); window.history.back();</script>";
                    break;
            }
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

// Cerrar la conexión al final
$conn->close();
?>