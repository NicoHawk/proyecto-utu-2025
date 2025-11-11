<?php
require_once __DIR__ . '/../db.php';

class Reserva {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function crear($usuario, $cargador_id, $inicio, $fin) {
        $sql = "SELECT COUNT(*) FROM reservas 
                WHERE cargador_id = ? AND estado = 'confirmada' 
                AND NOT (fin <= ? OR inicio >= ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$cargador_id, $inicio, $fin]);
        if ($stmt->fetchColumn() > 0) {
            return ['exito' => false, 'mensaje' => 'Horario no disponible'];
        }
        $sql = "INSERT INTO reservas (usuario, cargador_id, inicio, fin, estado) 
                VALUES (?, ?, ?, ?, 'confirmada')";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$usuario, $cargador_id, $inicio, $fin]);
        return ['exito' => $ok];
    }

    public function cancelar($usuario, $reserva_id) {
        $sql = "UPDATE reservas SET estado = 'cancelada' 
                WHERE id = ? AND usuario = ? AND estado = 'confirmada'";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$reserva_id, $usuario]);
        return ['exito' => $ok && $stmt->rowCount() > 0];
    }

    public function listarPorUsuario($usuario) {
        $sql = "SELECT r.*, c.nombre as estacion 
                FROM reservas r 
                LEFT JOIN cargadores c ON r.cargador_id = c.id 
                WHERE r.usuario = ? 
                ORDER BY r.inicio DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorCargador($cargador_id) {
        $sql = "SELECT * FROM reservas WHERE cargador_id = ? ORDER BY inicio";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$cargador_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarReservasCompletadas() {
        $ahora = date('Y-m-d H:i:s');
        $sql = "UPDATE reservas SET estado = 'completada' 
                WHERE estado = 'confirmada' AND fin < ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$ahora]);
    }
}
?>
