<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        // Listar autos: preparar la acción y cargar el controlador una sola vez
        $_POST = ['accion' => 'listar'];
        require_once __DIR__ . '/../controlador/AutoControlador.php';
        break;

    case 'POST':
        // Permitir también JSON puro
        $input = [];
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        // Funciones de usuario común
        if (isset($input['login'])) {
            $usuario = $input['usuario'] ?? '';
            $password = $input['password'] ?? '';
            require_once __DIR__ . '/../controlador/UsuarioControlador.php';
            echo json_encode(loginUsuario($usuario, $password));
        } elseif (isset($input['registro'])) {
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            $tipo_usuario = $input['tipo_usuario'] ?? 'cliente';
            require_once __DIR__ . '/../controlador/UsuarioControlador.php';
            echo json_encode(registrarUsuario($username, $password, $tipo_usuario));
        } elseif (isset($input['modificar'])) {
            $nombre = $input['nombre'] ?? '';
            $nuevoNombre = $input['nuevoNombre'] ?? '';
            $nuevoTipoUsuario = $input['nuevoTipoUsuario'] ?? 'cliente';
            $nuevaPassword = $input['nuevaPassword'] ?? '';
            require_once __DIR__ . '/../controlador/UsuarioControlador.php';
            echo json_encode(modificarUsuario($nombre, $nuevoNombre, $nuevoTipoUsuario, $nuevaPassword));
        } else if (isset($input['accion'])) {
            $_POST = $input; // Para que AutoControlador.php lo procese igual
            require_once __DIR__ . '/../controlador/AutoControlador.php';
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción POST no soportada']);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'editar') {
            $_POST = $input;
            require_once __DIR__ . '/../controlador/AutoControlador.php';
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción PUT no soportada']);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'eliminar') {
            $_POST = $input;
            require_once __DIR__ . '/../controlador/AutoControlador.php';
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción DELETE no soportada']);
        }
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Método no soportado']);
}
?>