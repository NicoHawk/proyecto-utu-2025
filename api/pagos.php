<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
ob_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../controlador/PagoControlador.php';

$pdo = conectar();

function out($d,$c=200){ if(ob_get_length())ob_clean(); http_response_code($c); echo json_encode($d,JSON_UNESCAPED_UNICODE); exit; }

if(!isset($_SESSION['usuario'])) out(['exito'=>false,'mensaje'=>'No autenticado'],401);

$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;
$ctrl = new PagoControlador($pdo);

try{
    switch($accion){
        case 'metodos':
            out($ctrl->listarMetodos());
        case 'iniciar':
            $reservaId = intval($_POST['reserva_id'] ?? ($_GET['reserva_id'] ?? 0));
            $metodoId  = intval($_POST['metodo_id'] ?? ($_GET['metodo_id'] ?? 0));
            $monto     = floatval($_POST['monto'] ?? ($_GET['monto'] ?? 0));
            out($ctrl->iniciar($reservaId, $_SESSION['usuario'], $metodoId, $monto));
        case 'confirmar':
            $pagoId = intval($_POST['pago_id'] ?? ($_GET['pago_id'] ?? 0));
            $estado = $_POST['estado'] ?? ($_GET['estado'] ?? 'aprobado');
            out($ctrl->confirmar($pagoId,$estado));
        case 'listar_usuario':
            $usuario = $_SESSION['usuario'];
            
            // Obtener pagos con info de reserva y método
            $sql = "SELECT p.id AS pago_id, 
                   p.reserva_id,
                   p.monto, 
                   p.moneda,
                   p.estado AS pago_estado,
                   p.creado_en AS fecha_pago,
                   mp.nombre AS metodo_nombre,
                   r.inicio AS reserva_inicio,
                   r.fin AS reserva_fin,
                   c.nombre AS estacion_nombre,
                   f.numero AS factura_numero,
                   f.pdf_path
            FROM pagos p
            LEFT JOIN metodos_pago mp ON mp.id = p.metodo_id
            LEFT JOIN reservas r ON r.id = p.reserva_id
            LEFT JOIN cargadores c ON c.id = r.cargador_id
            LEFT JOIN facturas f ON f.pago_id = p.id
            WHERE p.usuario_id = ?
            ORDER BY p.creado_en DESC";
            
            $st = $pdo->prepare($sql);
            $st->execute([$usuario]);
            $pagos = $st->fetchAll(PDO::FETCH_ASSOC);
            
            out(['exito'=>true, 'pagos'=>$pagos]);
        default:
            out(['exito'=>false,'mensaje'=>'Acción inválida'],400);
    }
} catch(Throwable $e){
    out(['exito'=>false,'mensaje'=>'Error en pagos','detalle'=>$e->getMessage()],500);
}