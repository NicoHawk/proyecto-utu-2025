<?php
require_once __DIR__ . '/../modelo/Pago.php';

class PagoControlador {
    private $pago;

    public function __construct(PDO $db){
        $this->pago = new Pago($db);
    }

    public function listarMetodos(){
        return ['exito'=>true,'metodos'=>$this->pago->obtenerMetodos()];
    }

    public function iniciar(int $reservaId, string $usuario, int $metodoId, float $monto){
        $res = $this->pago->reservaDeUsuario($reservaId,$usuario);
        if(!$res) return ['exito'=>false,'mensaje'=>'Reserva no encontrada o no pertenece al usuario'];
        if(intval($res['pagado'])===1) return ['exito'=>false,'mensaje'=>'Reserva ya pagada'];

        $id = $this->pago->iniciar($reservaId,$usuario,$metodoId,$monto);
        if(!$id) return ['exito'=>false,'mensaje'=>'Fallo al iniciar pago'];
        return ['exito'=>true,'pago_id'=>$id];
    }

    public function confirmar(int $pagoId, string $estado){
        if(!$this->pago->actualizarEstado($pagoId,$estado))
            return ['exito'=>false,'mensaje'=>'No se pudo actualizar estado'];

        if($estado==='aprobado'){
            $this->pago->marcarReservaPagada($pagoId);
        }
        return ['exito'=>true];
    }

    public function obtenerPagoReserva(int $reservaId){
        $p = $this->pago->obtenerPorReserva($reservaId);
        return ['exito'=>true,'pago'=>$p];
    }
}