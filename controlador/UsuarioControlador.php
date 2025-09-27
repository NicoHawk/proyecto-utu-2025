<?php

require_once __DIR__ . '/../modelo/Usuario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioModel = new Usuario();

function loginUsuario($usuario, $password) {
    global $usuarioModel;
    $usuarioData = $usuarioModel->verificarCredenciales($usuario, $password);
    if ($usuarioData) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['tipo_usuario'] = $usuarioData['tipo_usuario'];
        return [
            'success' => true,
            'tipo_usuario' => $usuarioData['tipo_usuario'],
            'mensaje' => 'Ingreso exitoso'
        ];
    } else {
        return [
            'success' => false,
            'tipo_usuario' => null,
            'mensaje' => 'Usuario o contraseña incorrectos'
        ];
    }
}

function registrarUsuario($username, $password, $tipo_usuario) {
    global $usuarioModel;
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    if ($usuarioModel->insertar($username, $passwordHash, $tipo_usuario)) {
        return [
            'success' => true,
            'mensaje' => 'Usuario registrado con éxito'
        ];
    } else {
        return [
            'success' => false,
            'mensaje' => 'Error al registrar usuario'
        ];
    }
}

function listarUsuarios() {
    global $usuarioModel;
    return $usuarioModel->listar();
}

function eliminarUsuario($nombre) {
    global $usuarioModel;
    if ($usuarioModel->eliminar($nombre)) {
        return [
            'success' => true,
            'mensaje' => 'Usuario eliminado'
        ];
    } else {
        return [
            'success' => false,
            'mensaje' => 'Error al eliminar'
        ];
    }
}

function modificarUsuario($nombre, $nuevoNombre, $nuevoTipoUsuario, $nuevaPassword = '') {
    global $usuarioModel;
    if ($nuevaPassword) {
        $ok = $usuarioModel->modificar($nombre, $nuevoNombre, $nuevoTipoUsuario, $nuevaPassword);
    } else {
        $ok = $usuarioModel->modificar($nombre, $nuevoNombre, $nuevoTipoUsuario);
    }
    if ($ok) {
        return [
            'success' => true,
            'mensaje' => 'Usuario actualizado'
        ];
    } else {
        return [
            'success' => false,
            'mensaje' => 'Error al actualizar'
        ];
    }
}


?>
