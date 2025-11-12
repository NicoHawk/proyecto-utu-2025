<?php
require_once __DIR__ . '/../modelo/Factura.php';
require_once __DIR__ . '/../modelo/Pago.php';
require_once __DIR__ . '/../modelo/Usuario.php';

class FacturaControlador {
    private $db;
    private $facturaModel;
    private $pagoModel;
    private $usuarioModel;

    public function __construct(PDO $db){
        $this->db = $db;
        $this->facturaModel = new Factura($db);
        $this->pagoModel    = new Pago($db);
        $this->usuarioModel = new Usuario($db);
    }

    public function generarSiNoExiste(int $pagoId, string $usuario){
        $p = $this->pagoModel->obtenerPorId($pagoId);
        if(!$p) return ['exito'=>false,'mensaje'=>'Pago inexistente'];
        if($p['estado']!=='aprobado') return ['exito'=>false,'mensaje'=>'Pago no aprobado'];
        
        // Verificar que el pago pertenece al usuario
        if($p['usuario_id'] !== $usuario) return ['exito'=>false,'mensaje'=>'No autorizado'];
        
        $ya = $this->facturaModel->obtenerPorPago($pagoId);
        if($ya){
            // Si ya existe PDF, devolver ruta
            if($ya['pdf_path']){
                return ['exito'=>true,'factura'=>$ya,'pdf'=>$ya['pdf_path']];
            }
            // Si no tiene PDF, generar
            $user = $this->usuarioModel->obtenerPorUsuario($usuario);
            $pdf = $this->facturaModel->generarPDF($ya, $p, $user);
            $ya['pdf_path'] = $pdf;
            return ['exito'=>true,'factura'=>$ya,'pdf'=>$pdf];
        }
        
        // Crear factura nueva
        $f = $this->facturaModel->generar($p);
        if(!$f) return ['exito'=>false,'mensaje'=>'No se pudo generar'];
        
        // Generar PDF
        $user = $this->usuarioModel->obtenerPorUsuario($usuario);
        $pdf = $this->facturaModel->generarPDF($f, $p, $user);
        $f['pdf_path'] = $pdf;
        
        return ['exito'=>true,'factura'=>$f,'pdf'=>$pdf];
    }
}