<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../controlador/ReservaControlador.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;

// Si viene JSON, extraer también la acción desde el cuerpo
$rawBody = file_get_contents('php://input');
$jsonBody = null;
if (!empty($rawBody) && isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $jsonBody = json_decode($rawBody, true);
    if (!$accion && is_array($jsonBody) && isset($jsonBody['accion'])) {
        $accion = $jsonBody['accion'];
    }
}

function responder($data, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario'])) {
    responder(['exito' => false, 'mensaje' => 'No autenticado'], 401);
}
$usuario = $_SESSION['usuario'];

try {
    if ($metodo === 'GET') {
        if ($accion === 'listar_usuario') {
            $resp = ReservaControlador::listarReservasUsuario($usuario);
            responder($resp);
        } elseif ($accion === 'listar_cargador') {
            $cargador_id = intval($_GET['cargador_id'] ?? 0);
            if ($cargador_id <= 0) responder(['exito' => false, 'mensaje' => 'cargador_id requerido'], 400);
            $resp = ReservaControlador::listarReservasCargador($cargador_id);
            responder($resp);
        } else {
            responder(['exito' => false, 'mensaje' => 'Acción GET no reconocida'], 400);
        }
    } elseif ($metodo === 'POST') {
        if ($accion === 'crear') {
            $input = is_array($jsonBody) ? $jsonBody : json_decode($rawBody, true);
            $cargador_id = intval($input['cargador_id'] ?? $_POST['cargador_id'] ?? 0);
            $inicio = $input['inicio'] ?? $_POST['inicio'] ?? null;
            $fin = $input['fin'] ?? $_POST['fin'] ?? null;
            if ($cargador_id <= 0 || !$inicio || !$fin) responder(['exito' => false, 'mensaje' => 'Datos incompletos'], 400);
            $resp = ReservaControlador::crearReserva($usuario, $cargador_id, $inicio, $fin);
            responder($resp, $resp['exito'] ? 200 : 409);
        } elseif ($accion === 'cancelar') {
            $input = is_array($jsonBody) ? $jsonBody : json_decode($rawBody, true);
            $reserva_id = intval($input['reserva_id'] ?? $_POST['reserva_id'] ?? 0);
            if ($reserva_id <= 0) responder(['exito' => false, 'mensaje' => 'reserva_id requerido'], 400);
            $resp = ReservaControlador::cancelarReserva($usuario, $reserva_id);
            responder($resp);
        } else {
            responder(['exito' => false, 'mensaje' => 'Acción POST no reconocida'], 400);
        }
    } else {
        responder(['exito' => false, 'mensaje' => 'Método no permitido'], 405);
    }
} catch (Exception $e) {
    responder(['exito' => false, 'mensaje' => 'Error del servidor', 'detalle' => $e->getMessage()], 500);
}
