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
        
        $ahora = date('Y-m-d H:i:s');
        
        foreach ($lista as &$c) {
            $sql = "SELECT COUNT(*) FROM reservas 
                    WHERE cargador_id = ? 
                    AND estado = 'confirmada' 
                    AND inicio <= ? 
                    AND fin > ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$c['id'], $ahora, $ahora]);
            $tieneReservaActiva = $stmt->fetchColumn() > 0;
            
            if ($tieneReservaActiva) {
                $c['estado'] = 'ocupado';
            } else {
                if ($c['estado'] !== 'en mantenimiento' && 
                    $c['estado'] !== 'fuera de servicio') {
                    $c['estado'] = 'disponible';
                }
            }
        }
        
        return $lista;
    }

    public function obtener($id) {
        $sql = "SELECT * FROM cargadores WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores) {
        $sql = "INSERT INTO cargadores (nombre, latitud, longitud, descripcion, tipo, estado, potencia_kw, conectores) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores]);
        return ['exito' => $ok, 'mensaje' => $ok ? 'Cargador creado con éxito' : 'Error al crear cargador'];
    }

    public function modificar($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores) {
        $sql = "UPDATE cargadores SET nombre=?, latitud=?, longitud=?, descripcion=?, tipo=?, estado=?, potencia_kw=?, conectores=? WHERE id=?";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores, $id]);
        return ['exito' => $ok, 'mensaje' => $ok ? 'Cargador actualizado con éxito' : 'Error al actualizar cargador'];
    }

    public function eliminar($id) {
        $sql = "DELETE FROM cargadores WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([$id]);
        return ['exito' => $ok, 'mensaje' => $ok ? 'Cargador eliminado con éxito' : 'Error al eliminar cargador'];
    }
}
?>
