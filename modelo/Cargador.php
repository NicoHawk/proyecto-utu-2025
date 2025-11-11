<?php
require_once __DIR__ . '/../db.php';

class Cargador {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function insertar($nombre, $latitud, $longitud, $descripcion = '', $tipo = '', $estado = 'disponible', $potencia_kw = 0, $conectores = '') {
        $sql = "INSERT INTO cargadores (nombre, latitud, longitud, descripcion, tipo, estado, potencia_kw, conectores) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores]);
    }

    public function listar() {
        $stmt = $this->conexion->query("SELECT * FROM cargadores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM cargadores WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM cargadores WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function modificar($id, $nombre, $latitud, $longitud, $descripcion, $tipo = '', $estado = 'disponible', $potencia_kw = 0, $conectores = '') {
        $sql = "UPDATE cargadores SET nombre = ?, latitud = ?, longitud = ?, descripcion = ?, tipo = ?, estado = ?, potencia_kw = ?, conectores = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores, $id]);
    }

    public function actualizarEstado($id, $estado) {
        $sql = "UPDATE cargadores SET estado = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$estado, $id]);
    }

    public function liberarCargadoresVencidos() {
        // Liberar cargadores cuyas reservas activas ya finalizaron
        $ahora = date('Y-m-d H:i:s');
        
        // Primero obtenemos los IDs de cargadores ocupados sin reservas activas
        $sql = "SELECT DISTINCT c.id 
                FROM cargadores c
                LEFT JOIN reservas r ON c.id = r.cargador_id 
                    AND r.estado = 'confirmada' 
                    AND r.fin > ?
                WHERE c.estado = 'ocupado' AND r.id IS NULL";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$ahora]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // Luego actualizamos esos cargadores
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sqlUpdate = "UPDATE cargadores SET estado = 'disponible' WHERE id IN ($placeholders)";
            $stmtUpdate = $this->conexion->prepare($sqlUpdate);
            return $stmtUpdate->execute($ids);
        }
        
        return true;
    }

    public function tieneReservaActiva($id) {
        $ahora = date('Y-m-d H:i:s');
        $sql = "SELECT COUNT(*) FROM reservas WHERE cargador_id = ? AND estado = 'confirmada' AND inicio <= ? AND fin > ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id, $ahora, $ahora]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
?>
