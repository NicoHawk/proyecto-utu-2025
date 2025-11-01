<?php
header('Content-Type: application/json');
// Evitar caché para que siempre se vean los cambios recientes
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    echo json_encode(['exito' => false, 'error' => 'No autorizado']);
    exit;
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

$accion = $data['accion'] ?? ($_GET['accion'] ?? '');

switch ($accion) {
    case 'listar':
        // Permitir elegir orden por query param (?orden=asc|desc). Por defecto, ascendente
        $orden = isset($_GET['orden']) ? $_GET['orden'] : 'asc';
        $autos = $autoModel->listarTodos($orden);
        echo json_encode($autos);
        break;

    case 'editar':
        $id        = isset($data['id']) ? (int)$data['id'] : 0;
        $modelo    = $data['modelo']   ?? '';
        $marca     = $data['marca']    ?? '';
        $conector  = $data['conector'] ?? '';
        $autonomia = isset($data['autonomia']) ? (int)$data['autonomia'] : 0;
        $anio      = isset($data['anio']) ? (int)$data['anio'] : 0;

        $ok = $autoModel->actualizarAdmin($id, $modelo, $marca, $conector, $autonomia, $anio);
        echo json_encode(['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto actualizado' : 'No se pudo actualizar']);
        break;

    case 'eliminar':
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $ok = $autoModel->eliminarAdmin($id);
        echo json_encode(['exito' => (bool)$ok, 'mensaje' => $ok ? 'Auto eliminado' : 'No se pudo eliminar']);
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Acción no válida']);
        break;
}
?>
