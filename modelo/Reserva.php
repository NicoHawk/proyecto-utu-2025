<?php
class Reserva {
    private $db;
    public function __construct(PDO $db){ $this->db = $db; }

    // Devuelve directamente el username (VARCHAR)
    public function usuarioIdPorUsuario(string $usuario){
        return $usuario;
    }

    // CORREGIDO: usa r.usuario en lugar de r.usuario_id
    public function listarPorUsuarioId(string $usuarioId){
        $sql = "SELECT r.id, r.cargador_id, r.inicio, r.fin, r.estado, r.monto, r.pagado,
                       c.nombre AS estacion
                FROM reservas r
                LEFT JOIN cargadores c ON c.id = r.cargador_id
                WHERE r.usuario=?
                ORDER BY r.inicio DESC";
        $st = $this->db->prepare($sql);
        $st->execute([$usuarioId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function marcarCompletadasSiVencidas(){
        $st = $this->db->prepare("UPDATE reservas SET estado='completada' WHERE estado='confirmada' AND fin < NOW()");
        $st->execute();
    }

    // CORREGIDO: INSERT usa usuario en lugar de usuario_id
    public function crear(string $usuarioId, int $cargadorId, string $inicio, string $fin){
        $chk = $this->db->prepare("SELECT id FROM cargadores WHERE id=?");
        $chk->execute([$cargadorId]);
        if(!$chk->fetch()) return ['exito'=>false,'mensaje'=>'Cargador inexistente'];

        $ins = $this->db->prepare("INSERT INTO reservas (cargador_id, usuario, inicio, fin, estado, monto, pagado)
                                   VALUES (?,?,?,?, 'confirmada', 0, 0)");
        if(!$ins->execute([$cargadorId,$usuarioId,$inicio,$fin])){
            return ['exito'=>false,'mensaje'=>'No se pudo crear la reserva'];
        }
        return ['exito'=>true,'id'=>$this->db->lastInsertId()];
    }

    // CORREGIDO: WHERE r.usuario en lugar de r.usuario_id
    public function cancelar(string $usuarioId, int $reservaId){
        $upd = $this->db->prepare("UPDATE reservas SET estado='cancelada'
                                   WHERE id=? AND usuario=? AND estado='confirmada'");
        $upd->execute([$reservaId,$usuarioId]);
        return ['exito'=>$upd->rowCount()>0];
    }

    public function obtenerPagoPorReserva(int $reservaId){
        $st = $this->db->prepare("SELECT p.id AS pago_id, p.estado, p.monto
                                  FROM pagos p
                                  WHERE p.reserva_id=? ORDER BY p.id DESC LIMIT 1");
        $st->execute([$reservaId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return [
            'exito'=>true,
            'pago_id'=>$row['pago_id']??null,
            'estado'=>$row['estado']??null,
            'monto'=>$row['monto']??null
        ];
    }
}
