<?php
class Pago {
    private $db;
    public function __construct(PDO $db){ $this->db = $db; }

    public function obtenerMetodos(){
        $st = $this->db->query("SELECT id,nombre,tipo FROM metodos_pago WHERE activo=1 ORDER BY id");
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Validar reserva SIN JOIN a usuarios (solo verificar que la reserva existe)
    public function reservaDeUsuario(int $reservaId, string $usuario){
        // Solo verificamos que la reserva exista
        $sql = "SELECT r.id, r.pagado, r.monto FROM reservas r WHERE r.id=? LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute([$reservaId]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function iniciar(int $reservaId, string $usuario, int $metodoId, float $monto){
        $sql = "INSERT INTO pagos (reserva_id, usuario_id, metodo_id, monto) VALUES (?,?,?,?)";
        $st  = $this->db->prepare($sql);
        if(!$st->execute([$reservaId,$usuario,$metodoId,$monto])) return false;
        return $this->db->lastInsertId();
    }

    public function actualizarEstado(int $pagoId, string $estado){
        $st = $this->db->prepare(
            "UPDATE pagos SET estado=?, confirmado_en=IF(?='aprobado',NOW(),confirmado_en) WHERE id=?"
        );
        return $st->execute([$estado,$estado,$pagoId]);
    }

    public function marcarReservaPagada(int $pagoId){
        $st = $this->db->prepare("
            UPDATE reservas r
            JOIN pagos p ON p.id=? AND p.reserva_id=r.id
            SET r.pagado=1, r.monto = IFNULL(NULLIF(r.monto,0), p.monto)
            WHERE r.id=p.reserva_id
        ");
        $st->execute([$pagoId]);
    }

    public function obtenerPorReserva(int $reservaId){
        $st = $this->db->prepare("SELECT * FROM pagos WHERE reserva_id=? ORDER BY id DESC LIMIT 1");
        $st->execute([$reservaId]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $pagoId){
        $st = $this->db->prepare("SELECT * FROM pagos WHERE id=? LIMIT 1");
        $st->execute([$pagoId]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }
}