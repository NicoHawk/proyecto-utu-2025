<?php

require_once __DIR__ . '/../modelo/Usuario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioModel = new Usuario();

function loginUsuario($correo, $password) {
    global $usuarioModel;
    $usuarioData = $usuarioModel->verificarCredenciales($correo, $password);
    if ($usuarioData) {
        $_SESSION['usuario'] = $usuarioData['usuario'];
        $_SESSION['correo'] = $usuarioData['correo'];
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
            'mensaje' => 'Correo o contraseña incorrectos'
        ];
    }
}

function registrarUsuario($username, $password, $tipo_usuario, $correo = '') {
    global $usuarioModel;
    // Si no se proporciona correo, generamos uno temporal
    if (empty($correo)) {
        $correo = $username . '@temp.com';
    }
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    if ($usuarioModel->insertar($username, $correo, $passwordHash, $tipo_usuario)) {
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

function modificarUsuario($nombre, $nuevoNombre, $nuevoCorreo = '', $nuevoTipoUsuario, $nuevaPassword = '') {
    global $usuarioModel;
    if ($nuevaPassword && $nuevoCorreo) {
        $ok = $usuarioModel->modificar($nombre, $nuevoNombre, $nuevoCorreo, $nuevoTipoUsuario, $nuevaPassword);
    } elseif ($nuevaPassword) {
        $ok = $usuarioModel->modificar($nombre, $nuevoNombre, null, $nuevoTipoUsuario, $nuevaPassword);
    } elseif ($nuevoCorreo) {
        $ok = $usuarioModel->modificar($nombre, $nuevoNombre, $nuevoCorreo, $nuevoTipoUsuario);
    } else {
        $ok = $usuarioModel->modificar($nombre, $nuevoNombre, null, $nuevoTipoUsuario);
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
