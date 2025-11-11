<?php
require_once __DIR__ . '/../modelo/Reserva.php';

class ReservaControlador {
    public static function crearReserva($usuario, $cargador_id, $inicio, $fin) {
        if (empty($usuario) || empty($cargador_id) || empty($inicio) || empty($fin)) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos'];
        }
        $reserva = new Reserva();
        return $reserva->crear($usuario, $cargador_id, $inicio, $fin);
    }

    public static function cancelarReserva($usuario, $reserva_id) {
        if (empty($usuario) || empty($reserva_id)) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos para cancelar'];
        }
        $reserva = new Reserva();
        return $reserva->cancelar($usuario, $reserva_id);
    }

    public static function listarReservasUsuario($usuario) {
        if (empty($usuario)) {
            return [];
        }
        $reserva = new Reserva();
        return $reserva->listarPorUsuario($usuario);
    }

    public static function listarReservasCargador($cargador_id) {
        if (empty($cargador_id)) {
            return [];
        }
        $reserva = new Reserva();
        return $reserva->listarPorCargador($cargador_id);
    }

    public static function marcarReservasCompletadas() {
        $reserva = new Reserva();
        return $reserva->marcarReservasCompletadas();
    }
}
?>
