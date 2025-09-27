<?php
require_once __DIR__ . '/../db.php';

class Usuario {
    private $conexion;
    private $tabla;

    public function __construct($tabla = 'usuarios') {
        $this->conexion = conectar();
        $this->tabla = $tabla;
    }

    // Cambiar la tabla objetivo (usuarios o usuarios_login)
    public function setTabla($tabla) {
        $this->tabla = $tabla;
    }

    // Registrar usuario (usuario, password, tipo_usuario)
    public function insertar($usuario, $password, $tipo_usuario) {
        $sql = "INSERT INTO {$this->tabla} (usuario, password, tipo_usuario) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario, $password, $tipo_usuario]);
    }

    // Verificar credenciales para login
    public function verificarCredenciales($usuario, $password) {
        $sql = "SELECT * FROM {$this->tabla} WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioData && password_verify($password, $usuarioData['password'])) {
            return $usuarioData;
        } else {
            return false;
        }
    }

    // Obtener tipo de usuario
    public function obtenerTipoUsuario($usuario) {
        $sql = "SELECT tipo_usuario FROM {$this->tabla} WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['tipo_usuario'] : null;
    }

    // Eliminar usuario por nombre de usuario
    public function eliminar($usuario) {
        $sql = "DELETE FROM {$this->tabla} WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$usuario]);
    }

    // Modificar nombre de usuario y tipo_usuario
    public function modificar($usuario, $nuevoUsuario, $nuevoTipoUsuario = null, $nuevaPassword = null) {
        if ($nuevaPassword) {
            $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE {$this->tabla} SET usuario = ?, tipo_usuario = ?, password = ? WHERE usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$nuevoUsuario, $nuevoTipoUsuario, $passwordHash, $usuario]);
        } else {
            $sql = "UPDATE {$this->tabla} SET usuario = ?, tipo_usuario = ? WHERE usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$nuevoUsuario, $nuevoTipoUsuario, $usuario]);
        }
    }

    // Listar todos los usuarios
    public function listar() {
        $stmt = $this->conexion->query("SELECT usuario, tipo_usuario FROM {$this->tabla}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por nombre
    public function obtener($usuario) {
        $sql = "SELECT * FROM {$this->tabla} WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>