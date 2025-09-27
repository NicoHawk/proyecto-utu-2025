<?php
require_once __DIR__ . '/../controlador/CargadorControlador.php';

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
        // Listar cargadores
        $_POST['accion'] = 'listar';
        require __DIR__ . '/../controlador/CargadorControlador.php';
        break;

    case 'POST':
        // Permitir también JSON puro
        $input = [];
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        if (isset($input['accion'])) {
            $_POST = $input; // Para que CargadorControlador.php lo procese igual
            require __DIR__ . '/../controlador/CargadorControlador.php';
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción POST no soportada']);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'agregar') {
            $_POST = $input;
            require __DIR__ . '/../controlador/CargadorControlador.php';
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción PUT no soportada']);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'eliminar') {
            $_POST = $input;
            require __DIR__ . '/../controlador/CargadorControlador.php';
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción DELETE no soportada']);
        }
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Método no soportado']);
}
?>