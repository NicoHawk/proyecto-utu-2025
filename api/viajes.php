<?php
session_start();
require_once __DIR__ . '/../controlador/ViajeControlador.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ViajeControlador.php define $viajeModel y funciones agregarViaje($viajeModel), listarViajes($viajeModel)

switch ($method) {
    case 'GET':
        $accion = $_GET['accion'] ?? '';
        if ($accion === 'listar') {
            listarViajes($viajeModel); // imprime JSON internamente
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Acción GET no soportada']);
        }
        break;
    case 'POST':
        // Aceptar tanto form-data/URL-encoded como JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            foreach ($input as $k => $v) { $_POST[$k] = $v; }
        }
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'agregar') {
            agregarViaje($viajeModel); // imprime JSON internamente
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Acción POST no soportada']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Método no soportado']);
}
