<?php
require_once __DIR__ . '/../modelo/Reserva.php';

class ReservaControlador {
    private $reserva;
    public function __construct(PDO $db){ $this->reserva = new Reserva($db); }

    public function listarReservasUsuario(string $usuario){
        $uid = $this->reserva->usuarioIdPorUsuario($usuario);
        if(!$uid) return [];
        return $this->reserva->listarPorUsuarioId($uid);
    }

    public function crear(string $usuario, int $cargadorId, string $inicio, string $fin){
        $uid = $this->reserva->usuarioIdPorUsuario($usuario);
        if(!$uid) return ['exito'=>false,'mensaje'=>'Usuario no encontrado'];
        return $this->reserva->crear($uid,$cargadorId,$inicio,$fin);
    }

    public function cancelar(string $usuario, int $reservaId){
        $uid = $this->reserva->usuarioIdPorUsuario($usuario);
        if(!$uid) return ['exito'=>false,'mensaje'=>'Usuario no encontrado'];
        return $this->reserva->cancelar($uid,$reservaId);
    }

    public function marcarReservasCompletadas(){
        $this->reserva->marcarCompletadasSiVencidas();
    }

    public function obtenerPagoPorReserva(int $reservaId){
        return $this->reserva->obtenerPagoPorReserva($reservaId);
    }
}
