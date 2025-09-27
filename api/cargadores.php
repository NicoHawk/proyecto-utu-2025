<?php
require_once __DIR__ . '/../controlador/CargadorControlador.php';
require_once __DIR__ . '/../db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

$conn = conectar();

switch ($method) {
    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        // Listar cargadores
        $cargadores = listarCargadores($conn);
        echo json_encode($cargadores);
        break;

    case 'POST':
        // Permitir también JSON puro
        $input = [];
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        if (isset($input['accion']) && $input['accion'] === 'agregar') {
            $nombre = $input['nombre'] ?? '';
            $latitud = $input['latitud'] ?? '';
            $longitud = $input['longitud'] ?? '';
            $res = agregarCargador($conn, $nombre, $latitud, $longitud);
            echo json_encode(['exito' => $res]);
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción POST no soportada']);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'eliminar') {
            $id = $input['id'] ?? null;
            $res = eliminarCargador($conn, $id);
            echo json_encode(['exito' => $res]);
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción DELETE no soportada']);
        }
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Método no soportado']);
}
?>