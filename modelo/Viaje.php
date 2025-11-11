<?php
require_once __DIR__ . '/../db.php';

class Viaje {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    public function insertar($usuario, $origen, $destino, $fecha, $distancia_km, $observaciones = null) {
        $sql = "INSERT INTO viajes (usuario, origen, destino, fecha, distancia_km, observaciones) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario, $origen, $destino, $fecha, $distancia_km, $observaciones]);
    }

    public function listarPorUsuario($usuario) {
        $sql = "SELECT * FROM viajes WHERE usuario = ? ORDER BY fecha DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>