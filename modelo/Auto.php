<?php
require_once __DIR__ . '/../db.php';

class Auto {
    private $conexion;

    public function __construct() {
        $this->conexion = conectar();
    }

    // === Métodos legacy (no utilizados actualmente) ===
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

    // === Nuevos métodos alineados al uso actual (usuario, conector, autonomia) ===
    public function insertarUsuario($usuario, $modelo, $marca, $conector, $autonomia, $anio, $bateria_actual = 100) {
        $sql = "INSERT INTO autos (usuario, modelo, marca, conector, autonomia, anio, bateria_actual) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario, $modelo, $marca, $conector, $autonomia, $anio, $bateria_actual]);
    }

    public function actualizarUsuario($id, $usuario, $modelo, $marca, $conector, $autonomia, $anio, $bateria_actual = null) {
        if ($bateria_actual !== null) {
            $sql = "UPDATE autos SET modelo = ?, marca = ?, conector = ?, autonomia = ?, anio = ?, bateria_actual = ? WHERE id = ? AND usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$modelo, $marca, $conector, $autonomia, $anio, $bateria_actual, $id, $usuario]);
        } else {
            $sql = "UPDATE autos SET modelo = ?, marca = ?, conector = ?, autonomia = ?, anio = ? WHERE id = ? AND usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$modelo, $marca, $conector, $autonomia, $anio, $id, $usuario]);
        }
    }

    public function eliminarDeUsuario($id, $usuario) {
        $sql = "DELETE FROM autos WHERE id = ? AND usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id, $usuario]);
    }

    public function listarPorUsuario($usuario) {
        $sql = "SELECT * FROM autos WHERE usuario = ? ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === Métodos para administradores ===
    public function listarTodos($orden = 'ASC') {
        // Normalizamos el orden para evitar inyección
        $orden = strtoupper($orden) === 'DESC' ? 'DESC' : 'ASC';
        // Listado global ordenado por ID (1,2,3... o inverso)
        $stmt = $this->conexion->query("SELECT * FROM autos ORDER BY id $orden");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarAdmin($id, $modelo, $marca, $conector, $autonomia, $anio, $bateria_actual = null) {
        if ($bateria_actual !== null) {
            $sql = "UPDATE autos SET modelo = ?, marca = ?, conector = ?, autonomia = ?, anio = ?, bateria_actual = ? WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$modelo, $marca, $conector, $autonomia, $anio, $bateria_actual, $id]);
        } else {
            $sql = "UPDATE autos SET modelo = ?, marca = ?, conector = ?, autonomia = ?, anio = ? WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$modelo, $marca, $conector, $autonomia, $anio, $id]);
        }
    }

    public function eliminarAdmin($id) {
        $sql = "DELETE FROM autos WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
