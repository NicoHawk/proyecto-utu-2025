<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controlador/ViajeControlador.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

switch ($method) {
    case 'GET':
        $accion = $_GET['accion'] ?? '';
        if ($accion === 'listar') {
            $usuario = $_SESSION['usuario'] ?? '';
            $viajes = listarViajesUsuario($usuario);
            echo json_encode($viajes);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Acción GET no soportada']);
        }
        break;

    case 'POST':
        // Aceptar tanto form-data/URL-encoded como JSON
        $input = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $input = $_POST;
        }
        
        $accion = $input['accion'] ?? '';
        if ($accion === 'agregar') {
            $usuario = $_SESSION['usuario'] ?? '';
            $origen = $input['origen'] ?? '';
            $destino = $input['destino'] ?? '';
            $fecha = $input['fecha'] ?? date('Y-m-d H:i:s');
            $distancia_km = $input['distancia_km'] ?? 0;
            $observaciones = $input['observaciones'] ?? null;
            
            $resultado = agregarViaje($usuario, $origen, $destino, $fecha, $distancia_km, $observaciones);
            echo json_encode($resultado);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Acción POST no soportada']);
        }
        break;

    default:
        echo json_encode(['exito' => false, 'mensaje' => 'Método no soportado']);
}
?>