<?php
require_once __DIR__ . '/../modelo/Reserva.php';

class ReservaControlador {
    public static function crearReserva($usuario, $cargador_id, $inicio, $fin) {
        $reserva = new Reserva();
        [$ok, $msg] = $reserva->crear($usuario, $cargador_id, $inicio, $fin);
        return ['exito' => $ok, 'mensaje' => $msg];
    }

    public static function cancelarReserva($usuario, $reserva_id) {
        $reserva = new Reserva();
        $ok = $reserva->cancelar($usuario, $reserva_id);
        return ['exito' => $ok];
    }

    public static function listarReservasUsuario($usuario) {
        $reserva = new Reserva();
        $lista = $reserva->listarPorUsuario($usuario);
        return ['exito' => true, 'reservas' => $lista];
    }

    public static function listarReservasCargador($cargador_id) {
        $reserva = new Reserva();
        $lista = $reserva->listarPorCargador($cargador_id);
        return ['exito' => true, 'reservas' => $lista];
    }

    public static function marcarReservasCompletadas() {
        $reserva = new Reserva();
        $ok = $reserva->marcarReservasCompletadas();
        return ['exito' => $ok];
    }
}
?>
