<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        // Listar autos del usuario autenticado
        require_once __DIR__ . '/../controlador/AutoControlador.php';
        $usuario = $_SESSION['usuario'] ?? '';
        $autos = listarAutosUsuario($usuario);
        echo json_encode($autos);
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
            require_once __DIR__ . '/../controlador/AutoControlador.php';
            $accion = $input['accion'];
            $usuario = $_SESSION['usuario'] ?? '';
            switch ($accion) {
                case 'agregar':
                    echo json_encode(agregarAutoUsuario(
                        $usuario,
                        $input['modelo'] ?? '',
                        $input['marca'] ?? '',
                        $input['conector'] ?? '',
                        $input['autonomia'] ?? 0,
                        $input['anio'] ?? 0
                    ));
                    break;
                case 'editar':
                    echo json_encode(editarAutoUsuario(
                        $usuario,
                        $input['id'] ?? 0,
                        $input['modelo'] ?? '',
                        $input['marca'] ?? '',
                        $input['conector'] ?? '',
                        $input['autonomia'] ?? 0,
                        $input['anio'] ?? 0
                    ));
                    break;
                case 'eliminar':
                    echo json_encode(eliminarAutoUsuario(
                        $usuario,
                        $input['id'] ?? 0
                    ));
                    break;
                case 'listar':
                    echo json_encode(listarAutosUsuario($usuario));
                    break;
                default:
                    echo json_encode(['exito' => false, 'error' => 'Acción no soportada']);
            }
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción POST no soportada']);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'editar') {
            require_once __DIR__ . '/../controlador/AutoControlador.php';
            $usuario = $_SESSION['usuario'] ?? '';
            echo json_encode(editarAutoUsuario(
                $usuario,
                $input['id'] ?? 0,
                $input['modelo'] ?? '',
                $input['marca'] ?? '',
                $input['conector'] ?? '',
                $input['autonomia'] ?? 0,
                $input['anio'] ?? 0
            ));
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción PUT no soportada']);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'eliminar') {
            require_once __DIR__ . '/../controlador/AutoControlador.php';
            $usuario = $_SESSION['usuario'] ?? '';
            echo json_encode(eliminarAutoUsuario($usuario, $input['id'] ?? 0));
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción DELETE no soportada']);
        }
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Método no soportado']);
}
?>