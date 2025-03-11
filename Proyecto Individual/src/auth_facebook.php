<?php
require 'vendor/autoload.php';

use Facebook\Facebook;

session_start();

// Si el usuario ya ha iniciado sesión, redirigir a home.php directamente
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$fb = new Facebook([
    'app_id' => '1814524866065112',
    'app_secret' => 'f87f3a2115884a9a325b4b8410400367',
    'default_graph_version' => 'v12.0',
]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email'];

if (!isset($_GET['code'])) {
    // Generar la URL de autenticación y redirigir al usuario
    try {
        $loginUrl = $helper->getLoginUrl('http://localhost/proyectoIS/auth_facebook.php', $permissions);
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

            // Verificar si el usuario ya existe
            $stmt = $conn->prepare("SELECT role_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($role_id);
            $stmt->fetch();
            $stmt->close();

            if ($role_id === null) {
                // Usuario no existe, insertar y asignar rol predeterminado (rol 2: usuario)
                $role_id = 2;
                $stmt = $conn->prepare("INSERT INTO users (username, email, role_id) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $username, $email, $role_id);
                $stmt->execute();
            }

            // Configurar las variables de sesión
            $_SESSION['user_id'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['role_id'] = $role_id;

            // Redirigir a home.php
            header('Location: home.php');
            exit();
        } else {
            die("Error: Los datos del usuario no son válidos.");
        }
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Error durante la autenticación con Facebook: ' . $e->getMessage();
        exit();
    }
}
