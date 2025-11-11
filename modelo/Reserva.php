<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Cargador.php';

class Reserva {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function crear($usuario, $cargador_id, $inicio, $fin) {
        // Chequear solapamientos
        $sql = "SELECT COUNT(*) FROM reservas WHERE cargador_id = ? AND estado <> 'cancelada' AND ((inicio < ? AND fin > ?) OR (inicio >= ? AND inicio < ?) OR (fin > ? AND fin <= ?))";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$cargador_id, $fin, $inicio, $inicio, $fin, $inicio, $fin]);
        $count = (int)$stmt->fetchColumn();
        if ($count > 0) return [false, 'Horario no disponible'];

        $sqlIns = "INSERT INTO reservas (usuario, cargador_id, inicio, fin, estado) VALUES (?, ?, ?, ?, 'confirmada')";
        $stmtIns = $this->conexion->prepare($sqlIns);
        $ok = $stmtIns->execute([$usuario, $cargador_id, $inicio, $fin]);

        if ($ok) {
            // Verificar si la reserva recién creada está activa AHORA
            $cargador = new Cargador();
            if ($cargador->tieneReservaActiva($cargador_id)) {
                // Hay reserva activa ahora, marcar ocupado
                $cargador->actualizarEstado($cargador_id, 'ocupado');
            }
        }
        
        return [$ok, $ok ? 'Reserva creada' : 'No se pudo crear la reserva'];
    }

    public function cancelar($usuario, $reserva_id) {
        // Obtener info de la reserva antes de cancelar
        $sqlGet = "SELECT cargador_id FROM reservas WHERE id = ? AND usuario = ?";
        $stmtGet = $this->conexion->prepare($sqlGet);
        $stmtGet->execute([$reserva_id, $usuario]);
        $reserva = $stmtGet->fetch(PDO::FETCH_ASSOC);
        
        if (!$reserva) return false;
        
        $sql = "UPDATE reservas SET estado = 'cancelada' WHERE id = ? AND usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$reserva_id, $usuario]);
        
        if ($ok) {
            // Verificar si el cargador tiene otras reservas activas
            $cargador = new Cargador();
            if (!$cargador->tieneReservaActiva($reserva['cargador_id'])) {
                $cargador->actualizarEstado($reserva['cargador_id'], 'disponible');
            }
        }
        
        return $ok;
    }

    public function listarPorUsuario($usuario) {
        $sql = "SELECT r.*, c.nombre AS estacion FROM reservas r JOIN cargadores c ON c.id = r.cargador_id WHERE r.usuario = ? ORDER BY r.inicio DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorCargador($cargador_id) {
        $sql = "SELECT * FROM reservas WHERE cargador_id = ? AND estado <> 'cancelada' ORDER BY inicio";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$cargador_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarReservasCompletadas() {
        // Marcar como completadas las reservas cuyo fin ya pasó
        $ahora = date('Y-m-d H:i:s');
        $sql = "UPDATE reservas SET estado = 'completada' 
                WHERE estado = 'confirmada' AND fin <= ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$ahora]);
    }
}
?>
