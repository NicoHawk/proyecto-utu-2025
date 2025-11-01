<?php
// Controlador orientado a modelo (sin conexiones directas aquí)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelo/Auto.php';

// Funciones del controlador que pueden ser llamadas desde la API

function listarAutosAdmin($orden = 'asc') {
    $autoModel = new Auto();
    return $autoModel->listarTodos($orden);
}

function agregarAutoAdmin($usuario, $modelo, $marca, $conector, $autonomia, $anio) {
    if (empty($usuario)) {
        return ['exito' => false, 'mensaje' => 'Debe seleccionar un usuario'];
    }
    $autoModel = new Auto();
    $ok = $autoModel->insertarUsuario($usuario, $modelo, $marca, $conector, $autonomia, $anio);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto agregado correctamente' : 'No se pudo agregar el auto'];
}

function editarAutoAdmin($id, $modelo, $marca, $conector, $autonomia, $anio) {
    $autoModel = new Auto();
    $ok = $autoModel->actualizarAdmin($id, $modelo, $marca, $conector, $autonomia, $anio);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto actualizado' : 'No se pudo actualizar'];
}

function eliminarAutoAdmin($id) {
    $autoModel = new Auto();
    $ok = $autoModel->eliminarAdmin($id);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto eliminado' : 'No se pudo eliminar'];
}

// ===== Funciones para USUARIO (no admin) =====
function listarAutosUsuario($usuario) {
    if (empty($usuario)) { return []; }
    $autoModel = new Auto();
    return $autoModel->listarPorUsuario($usuario);
}

function agregarAutoUsuario($usuario, $modelo, $marca, $conector, $autonomia, $anio) {
    if (empty($usuario)) {
        return ['exito' => false, 'mensaje' => 'No autenticado'];
    }
    $autoModel = new Auto();
    $ok = $autoModel->insertarUsuario($usuario, $modelo, $marca, $conector, (int)$autonomia, (int)$anio);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto agregado' : 'No se pudo agregar'];
}

function editarAutoUsuario($usuario, $id, $modelo, $marca, $conector, $autonomia, $anio) {
    if (empty($usuario)) { return ['exito' => false, 'mensaje' => 'No autenticado']; }
    $autoModel = new Auto();
    $ok = $autoModel->actualizarUsuario((int)$id, $usuario, $modelo, $marca, $conector, (int)$autonomia, (int)$anio);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto actualizado' : 'No se pudo actualizar'];
}

function eliminarAutoUsuario($usuario, $id) {
    if (empty($usuario)) { return ['exito' => false, 'mensaje' => 'No autenticado']; }
    $autoModel = new Auto();
    $ok = $autoModel->eliminarDeUsuario((int)$id, $usuario);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto eliminado' : 'No se pudo eliminar'];
}

// Si se llama directamente como API (legacy)
if (basename($_SERVER['SCRIPT_FILENAME']) === 'AutoControlador.php') {
    header('Content-Type: application/json');
    
    $autoModel = new Auto();

    // Detectar si la petición es JSON o POST tradicional
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if ($contentType && strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $data = $_POST ?? [];
    }

    $accion = $data['accion'] ?? '';

    switch ($accion) {
        case 'agregar':
            if (!isset($_SESSION['usuario'])) {
                echo json_encode(['exito' => false, 'error' => 'No autenticado']);
                break;
            }
            $usuario   = $_SESSION['usuario'];
            $modelo    = $data['modelo']   ?? '';
            $marca     = $data['marca']    ?? '';
            $conector  = $data['conector'] ?? '';
            $autonomia = isset($data['autonomia']) ? (int)$data['autonomia'] : 0;
            $anio      = isset($data['anio']) ? (int)$data['anio'] : 0;

            $ok = $autoModel->insertarUsuario($usuario, $modelo, $marca, $conector, $autonomia, $anio);
            echo json_encode(['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto agregado' : 'No se pudo agregar']);
            break;

        case 'editar':
            if (!isset($_SESSION['usuario'])) {
                echo json_encode(['exito' => false, 'error' => 'No autenticado']);
                break;
            }
            $usuario   = $_SESSION['usuario'];
            $id        = isset($data['id']) ? (int)$data['id'] : 0;
            $modelo    = $data['modelo']   ?? '';
            $marca     = $data['marca']    ?? '';
            $conector  = $data['conector'] ?? '';
            $autonomia = isset($data['autonomia']) ? (int)$data['autonomia'] : 0;
            $anio      = isset($data['anio']) ? (int)$data['anio'] : 0;

            $ok = $autoModel->actualizarUsuario($id, $usuario, $modelo, $marca, $conector, $autonomia, $anio);
            echo json_encode(['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto actualizado' : 'No se pudo actualizar']);
            break;

        case 'eliminar':
            if (!isset($_SESSION['usuario'])) {
                echo json_encode(['exito' => false, 'error' => 'No autenticado']);
                break;
            }
            $usuario = $_SESSION['usuario'];
            $id      = isset($data['id']) ? (int)$data['id'] : 0;
            $ok = $autoModel->eliminarDeUsuario($id, $usuario);
            echo json_encode(['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto eliminado' : 'No se pudo eliminar']);
            break;

        case 'listar':
            if (!isset($_SESSION['usuario'])) {
                echo json_encode([]);
                break;
            }
            $usuario = $_SESSION['usuario'];
            $autos = $autoModel->listarPorUsuario($usuario);
            echo json_encode($autos);
            break;

        default:
            echo json_encode(['exito' => false, 'error' => 'Acción no válida']);
            break;
    }
}
?>