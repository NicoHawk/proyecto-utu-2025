<?php
require_once __DIR__ . '/../controlador/UsuarioControlador.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'cliente';
    $resultado = registrarUsuario($username, $password, $tipo_usuario, $correo);
    echo json_encode($resultado);
    exit;
}

echo json_encode(['success' => false, 'mensaje' => 'Método no soportado']);
exit;
