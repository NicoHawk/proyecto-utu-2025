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
        $cargadores = listarCargadores();
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
            $descripcion = $input['descripcion'] ?? '';
            $tipo = $input['tipo'] ?? '';
            $estado = $input['estado'] ?? 'disponible';
            $potencia_kw = $input['potencia_kw'] ?? 0;
            $conectores = $input['conectores'] ?? '';
            $res = agregarCargador($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores);
            echo json_encode($res);
        } else if (isset($input['accion']) && $input['accion'] === 'modificar') {
            $id = $input['id'] ?? 0;
            $nombre = $input['nombre'] ?? '';
            $latitud = $input['latitud'] ?? '';
            $longitud = $input['longitud'] ?? '';
            $descripcion = $input['descripcion'] ?? '';
            $tipo = $input['tipo'] ?? '';
            $estado = $input['estado'] ?? 'disponible';
            $potencia_kw = $input['potencia_kw'] ?? 0;
            $conectores = $input['conectores'] ?? '';
            $res = modificarCargador($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores);
            echo json_encode($res);
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción POST no soportada']);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input && isset($input['accion']) && $input['accion'] === 'eliminar') {
            $id = $input['id'] ?? null;
            $res = eliminarCargador($id);
            echo json_encode($res);
        } else {
            echo json_encode(['exito' => false, 'error' => 'Acción DELETE no soportada']);
        }
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Método no soportado']);
}
?>