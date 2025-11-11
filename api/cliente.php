<?php
session_start();
require_once __DIR__ . '/../controlador/UsuarioControlador.php';

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
        if (isset($_GET['listar'])) {
            echo json_encode(listarUsuarios());
        } else {
            echo json_encode(['error' => 'Acción GET no soportada']);
        }
        break;

    case 'POST':
        if (isset($_POST['eliminar'])) {
            $nombre = $_POST['nombre'] ?? '';
            echo json_encode(eliminarUsuario($nombre));
        } elseif (isset($_POST['modificar_perfil'])) {
            $nombre = $_SESSION['usuario'] ?? '';
            $nuevoNombre = $_POST['nuevoNombre'] ?? '';
            $nuevoCorreo = $_POST['nuevoCorreo'] ?? '';
            $nuevaPassword = $_POST['nuevaPassword'] ?? '';
            $tipoUsuario = $_SESSION['tipo_usuario'] ?? 'cliente';
            
            // Llamar a modificarUsuario con los parámetros en el orden correcto
            $resultado = modificarUsuario($nombre, $nuevoNombre, $nuevoCorreo, $tipoUsuario, $nuevaPassword);
            
            if ($resultado['success']) {
                // Actualizar la sesión con los nuevos datos
                $_SESSION['usuario'] = $nuevoNombre;
                $_SESSION['correo'] = $nuevoCorreo;
            }
            echo json_encode($resultado);
        } elseif (isset($_POST['modificar'])) {
            $nombre = $_POST['nombre'] ?? '';
            $nuevoNombre = $_POST['nuevoNombre'] ?? '';
            $nuevoCorreo = $_POST['nuevoCorreo'] ?? '';
            $nuevoTipoUsuario = $_POST['nuevoTipoUsuario'] ?? 'cliente';
            $nuevaPassword = $_POST['nuevaPassword'] ?? '';
            echo json_encode(modificarUsuario($nombre, $nuevoNombre, $nuevoCorreo, $nuevoTipoUsuario, $nuevaPassword));
        } else {
            echo json_encode(['error' => 'Acción POST no soportada']);
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        $nombre = $_PUT['nombre'] ?? '';
        $nuevoNombre = $_PUT['nuevoNombre'] ?? '';
        $nuevoTipoUsuario = $_PUT['nuevoTipoUsuario'] ?? 'cliente';
        $nuevaPassword = $_PUT['nuevaPassword'] ?? '';
        echo json_encode(modificarUsuario($nombre, $nuevoNombre, $nuevoTipoUsuario, $nuevaPassword));
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        $nombre = $_DELETE['nombre'] ?? '';
        echo json_encode(eliminarUsuario($nombre));
        break;

    default:
        echo json_encode(['error' => 'Método no soportado']);
}