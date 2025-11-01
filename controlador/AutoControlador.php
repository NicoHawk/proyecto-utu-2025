<?php
// Controlador orientado a modelo (sin conexiones directas aquí)
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelo/Auto.php';

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
?>