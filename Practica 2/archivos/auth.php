<?php
require 'vendor/autoload.php';

use Facebook\Facebook;
use Google\Client;
 
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';

// Función para verificar/crear usuario y configurar sesión
function procesarUsuario($conn, $username, $email, $role_id = null, $password_hash = null) {
    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Usuario existe, obtener su rol
        $stmt->bind_result($role_id);
        $stmt->fetch();
    } else {
        // Usuario no existe, insertar nuevo con rol predeterminado (2: usuario)
        if ($role_id === null) $role_id = 2;
        
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $email, $password_hash, $role_id);
        $stmt->execute();
    }
    
    $stmt->close();
    
    // Configurar variables de sesión
    $_SESSION['user_id'] = $email;
    $_SESSION['username'] = $username;
    $_SESSION['role_id'] = $role_id;
    
    return true;
}

// Manejar registro de usuarios normal
function registrarUsuario($conn, $username, $email, $password) {
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
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
    if (procesarUsuario($conn, $username, $email, 2, $password_hash)) {
        return "registro_exitoso";
    } else {
        return "error_registro";
    }
}

// Manejar inicio de sesión normal
function iniciarSesion($conn, $username, $password) {
    // Buscar usuario en la base de datos
    $stmt = $conn->prepare("SELECT id, username, password, role_id, email FROM users WHERE BINARY username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $db_password, $role_id, $email);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION['user_id'] = $email;
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

// Función para manejar la autenticación Facebook
function autenticarConFacebook() {
    // Si el usuario ya ha iniciado sesión, redirigir directamente
    if (isset($_SESSION['user_id'])) {
        header('Location: instorve.php');
        exit();
    }
    
    $fb = new Facebook([
        'app_id' => '1708245636394836',
        'app_secret' => '52c699071afc62b6b450fb4841295384',
        'default_graph_version' => 'v22.0',
    ]);

    $helper = $fb->getRedirectLoginHelper();
    $permissions = ['email'];

    if (!isset($_GET['code'])) {
        // Generar la URL de autenticación y redirigir al usuario
        try {
            $loginUrl = $helper->getLoginUrl('http://localhost/IStoryVerse/archivos/auth.php?provider=facebook', $permissions);
            header('Location: ' . $loginUrl);
            exit();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Error en Facebook SDK: ' . $e->getMessage();
            exit();
        }
    } else {
        try {
            // Obtener el token de acceso
            $accessToken = $helper->getAccessToken();
            if (!isset($accessToken)) {
                echo "No se pudo obtener el token de acceso.";
                exit();
            }

            // Obtener datos del usuario desde Facebook
            $response = $fb->get('/me?fields=id,name,email', $accessToken);
            $user = $response->getGraphUser();

            // Guardar la información del usuario
            $username = $user['name'];
            $email = $user['email'];

            if (!empty($username) && !empty($email)) {
                $conn = new mysqli('localhost', 'root', '', 'spring_auth');
                if ($conn->connect_error) {
                    die("Error de conexión: " . $conn->connect_error);
                }

                procesarUsuario($conn, $username, $email);
                
                // Redirigir a la página principal
                header('Location: instorve.php');
                exit();
            } else {
                die("Error: Los datos del usuario no son válidos.");
            }
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Error durante la autenticación con Facebook: ' . $e->getMessage();
            exit();
        }
    }  
}

// Función para manejar la autenticación Google
function autenticarConGoogle() {
    // Si el usuario ya ha iniciado sesión, redirigir directamente
    if (isset($_SESSION['user_id'])) {
        header('Location: instorve.php');
        exit();
    }

    $client = new Client();
    $client->setClientId('44081940362-k12f6lrgeplmra57fb14fvdj47upjehp.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://localhost/IStoryVerse/archivos/auth.php?provider=google');
    $client->addScope('email');
    $client->addScope('profile');

    if (!isset($_GET['code'])) {
        // Generar la URL de autenticación y redirigir al usuario
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();
    } else {
        try {
            $client->authenticate($_GET['code']);
            $token = $client->getAccessToken();
            $client->setAccessToken($token);

            $oauth2 = new \Google\Service\Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            // Extraer información del usuario
            $username = $userInfo->name;
            $email = $userInfo->email;

            if (!empty($username) && !empty($email)) {
                $conn = new mysqli('localhost', 'root', '', 'spring_auth');
                if ($conn->connect_error) {
                    die("Error de conexión: " . $conn->connect_error);
                }

                procesarUsuario($conn, $username, $email);
                
                // Redirigir a la página principal
                header('Location: instorve.php');
                exit();
            } else {
                die("Error: Los datos del usuario no son válidos.");
            }
        } catch (Exception $e) {
            echo "Error durante el inicio de sesión: " . $e->getMessage();
            exit();
        }
    }
}

// Función para cerrar sesión
function cerrarSesion() {
    session_destroy();
    header("Location: index.html");
    exit();
}

// CONTROLADOR PRINCIPAL

// Verificar si es una solicitud de logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    cerrarSesion();
}

// Verificar si es una solicitud de autenticación con proveedor externo
if (isset($_GET['provider'])) {
    $provider = $_GET['provider'];
    
    switch ($provider) {
        case 'facebook':
            autenticarConFacebook();
            break;
        case 'google':
            autenticarConGoogle();
            break;
        default:
            header("Location: index.html");
            exit();
    }
}

// Procesar solicitudes POST (login/registro normal)
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
                    header("Location: instorve.php");
                    exit();
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

// Protección de acceso (redirigir si no hay sesión)
if (!isset($_GET['provider']) && !isset($_GET['action']) && !isset($_POST['action']) && !isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Cerrar la conexión al final
if (isset($conn) && $conn) {
    $conn->close();
}
?>
