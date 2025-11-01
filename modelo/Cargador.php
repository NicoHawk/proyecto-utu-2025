<?php
require_once __DIR__ . '/../db.php';

class Cargador {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function insertar($nombre, $latitud, $longitud, $descripcion = '') {
        $sql = "INSERT INTO cargadores (nombre, latitud, longitud, descripcion) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre, $latitud, $longitud, $descripcion]);
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

    public function modificar($id, $nombre, $latitud, $longitud, $descripcion) {
        $sql = "UPDATE cargadores SET nombre = ?, latitud = ?, longitud = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre, $latitud, $longitud, $descripcion, $id]);
    }
}
?>
