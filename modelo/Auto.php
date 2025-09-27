<?php
require_once __DIR__ . '/../db.php';

class Auto {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function insertar($marca, $modelo, $anio, $usuario_id) {
        $sql = "INSERT INTO autos (marca, modelo, anio, usuario_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$marca, $modelo, $anio, $usuario_id]);
    }

    public function listar() {
        $stmt = $this->conexion->query("SELECT * FROM autos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM autos WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM autos WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function modificar($id, $marca, $modelo, $anio) {
        $sql = "UPDATE autos SET marca = ?, modelo = ?, anio = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$marca, $modelo, $anio, $id]);
    }
}
?>
