<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
session_start();
ob_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../controlador/ReservaControlador.php';

$pdo = conectar();

function responder($d,$c=200){
    if (ob_get_length()) ob_clean();
    http_response_code($c);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario'])) responder(['exito'=>false,'mensaje'=>'No autenticado'],401);
$usuario = $_SESSION['usuario'];

try {
    $ctrl = new ReservaControlador($pdo);
} catch (Throwable $e) {
    responder(['exito'=>false,'mensaje'=>'Error al crear controlador','detalle'=>$e->getMessage()],500);
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;
$raw = file_get_contents('php://input');
$isJson = isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'],'application/json')!==false;
$body = $isJson ? json_decode($raw,true) : null;
if(!$accion && is_array($body) && isset($body['accion'])) $accion = $body['accion'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($accion) {
            case 'listar_usuario':
                $ctrl->marcarReservasCompletadas();
                $reservas = $ctrl->listarReservasUsuario($usuario);
                responder(['exito'=>true,'reservas'=>$reservas]);
            case 'detalle_pago_reserva':
                $id = intval($_GET['id'] ?? 0);
                responder($ctrl->obtenerPagoPorReserva($id));
            default:
                responder(['exito'=>false,'mensaje'=>'Acción GET no reconocida'],400);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = is_array($body) ? $body : $_POST;
        switch ($accion) {
            case 'crear':
                $cargadorId = intval($data['cargador_id'] ?? 0);
                $inicio     = trim($data['inicio'] ?? '');
                $fin        = trim($data['fin'] ?? '');
                if ($cargadorId<=0 || !$inicio || !$fin) responder(['exito'=>false,'mensaje'=>'Datos incompletos'],400);
                responder($ctrl->crear($usuario,$cargadorId,$inicio,$fin));
            case 'cancelar':
                $reservaId = intval($data['reserva_id'] ?? 0);
                if ($reservaId<=0) responder(['exito'=>false,'mensaje'=>'reserva_id requerido'],400);
                responder($ctrl->cancelar($usuario,$reservaId));
            default:
                responder(['exito'=>false,'mensaje'=>'Acción POST no reconocida'],400);
        }
    } else {
        responder(['exito'=>false,'mensaje'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    responder(['exito'=>false,'mensaje'=>'Error en servidor','detalle'=>$e->getMessage(),'trace'=>$e->getTraceAsString()],500);
}
