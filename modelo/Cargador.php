<?php
require_once __DIR__ . '/../db.php';

class Cargador {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function listar() {
        $sql = "SELECT * FROM cargadores ORDER BY id";
        $stmt = $this->conexion->query($sql);
        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // CALCULAR ESTADO DINÁMICO BASADO EN RESERVAS ACTIVAS
        $ahora = date('Y-m-d H:i:s');
        
        foreach ($lista as &$c) {
            // Verificar si tiene reservas activas en este momento
            $sql = "SELECT COUNT(*) FROM reservas 
                    WHERE cargador_id = ? 
                    AND estado = 'confirmada' 
                    AND inicio <= ? 
                    AND fin > ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$c['id'], $ahora, $ahora]);
            $tieneReservaActiva = $stmt->fetchColumn() > 0;
            
            // Si tiene reserva activa, marcar como ocupado
            if ($tieneReservaActiva) {
                $c['estado'] = 'ocupado';
            } else {
                // Si no tiene reserva activa y no está en mantenimiento, marcar como disponible
                if ($c['estado'] !== 'en mantenimiento' && 
                    $c['estado'] !== 'fuera de servicio') {
                    $c['estado'] = 'disponible';
                }
            }
        }
        
        return $lista;
    }

    public function crear($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores) {
        $sql = "INSERT INTO cargadores (nombre, latitud, longitud, descripcion, tipo, estado, potencia_kw, conectores) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores]);
        return ['exito' => $ok];
    }

    public function modificar($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores) {
        $sql = "UPDATE cargadores SET nombre=?, latitud=?, longitud=?, descripcion=?, tipo=?, estado=?, potencia_kw=?, conectores=? WHERE id=?";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores, $id]);
        return ['exito' => $ok];
    }

    public function eliminar($id) {
        $sql = "DELETE FROM cargadores WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$id]);
        return ['exito' => $ok];
    }
}
?>
