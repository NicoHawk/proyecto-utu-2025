<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
ob_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../controlador/FacturaControlador.php';

$pdo = conectar();

function out($d,$c=200){ if(ob_get_length())ob_clean(); http_response_code($c); echo json_encode($d,JSON_UNESCAPED_UNICODE); exit; }

if(!isset($_SESSION['usuario'])) out(['exito'=>false,'mensaje'=>'No autenticado'],401);

$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;
$ctrl = new FacturaControlador($pdo);

try{
    switch($accion){
        case 'generar':
            $pagoId = intval($_POST['pago_id'] ?? ($_GET['pago_id'] ?? 0));
            $resultado = $ctrl->generarSiNoExiste($pagoId, $_SESSION['usuario']);
            out($resultado);
        
        case 'descargar':
            $pagoId = intval($_GET['pago_id'] ?? 0);
            $resultado = $ctrl->generarSiNoExiste($pagoId, $_SESSION['usuario']);
            if(!$resultado['exito']) out($resultado);
            
            // Servir PDF como descarga
            $rutaPDF = __DIR__ . '/../facturas/' . $resultado['pdf'];
            if(!file_exists($rutaPDF)) out(['exito'=>false,'mensaje'=>'PDF no encontrado']);
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($rutaPDF) . '"');
            header('Content-Length: ' . filesize($rutaPDF));
            readfile($rutaPDF);
            exit;
        
        default:
            out(['exito'=>false,'mensaje'=>'Acción inválida'],400);
    }
} catch(Throwable $e){
    out(['exito'=>false,'mensaje'=>'Error en facturas','detalle'=>$e->getMessage()],500);
}