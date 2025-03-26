<?php
require '../vendor/autoload.php';

use Google\Client;

session_start();

// Si el usuario ya ha iniciado sesión, redirigir a home.php directamente
if (isset($_SESSION['user_id'])) {
    header('Location: instorve.php');
    exit();
}

$client = new Client();
$client->setClientId('44081940362-k12f6lrgeplmra57fb14fvdj47upjehp.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/IStoryVerse/archivos/auth_google.php');
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
