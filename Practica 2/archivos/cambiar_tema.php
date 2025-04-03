<?php
session_start();

// Validar que solo se acepten modos específicos
$modos_validos = ['claro', 'oscuro', 'sistema'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tema = $_POST['tema'] ?? 'sistema';
    
    if (in_array($tema, $modos_validos)) {
        $_SESSION['tema'] = $tema;
        echo json_encode(['status' => 'success', 'message' => 'Tema cambiado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tema inválido']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>
