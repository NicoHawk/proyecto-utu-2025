<?php

require_once __DIR__ . '/../controlador/UsuarioControlador.php';
require_once __DIR__ . '/../controlador/CargadorControlador.php';
require_once __DIR__ . '/../controlador/AutoControlador.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
// Evitar caché para que siempre se vean los cambios recientes
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea administrador para ciertas operaciones
function verificarAdmin() {
    if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
        echo json_encode(['exito' => false, 'error' => 'No autorizado']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        if (isset($_GET['listar_usuarios'])) {
            echo json_encode(listarUsuarios());
        } elseif (isset($_GET['listar_cargadores'])) {
            echo json_encode(listarCargadores());
        } elseif (isset($_GET['listar_autos'])) {
            // Listar autos - requiere ser admin
            verificarAdmin();
            $orden = isset($_GET['orden']) ? $_GET['orden'] : 'asc';
            $autos = listarAutosAdmin($orden);
            echo json_encode($autos);
        } else {
            echo json_encode(['error' => 'Acción GET no soportada']);
        }
        break;

    case 'POST':
        // Detectar si la petición es JSON o POST tradicional
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $data = [];
        if ($contentType && strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $data = $_POST;
        }

        // USUARIOS
        if (isset($_POST['agregar_usuario'])) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $tipo_usuario = $_POST['tipo_usuario'] ?? 'cliente';
            $correo = $_POST['correo'] ?? ''; // Agregar soporte para correo
            echo json_encode(registrarUsuario($username, $password, $tipo_usuario, $correo));
        } elseif (isset($_POST['eliminar_usuario'])) {
            $nombre = $_POST['nombre'] ?? '';
            echo json_encode(eliminarUsuario($nombre));
        } elseif (isset($_POST['modificar_usuario'])) {
            $nombre = $_POST['nombre'] ?? '';
            $nuevoNombre = $_POST['nuevoNombre'] ?? '';
            $nuevoCorreo = $_POST['nuevoCorreo'] ?? '';
            $nuevoTipoUsuario = $_POST['nuevoTipoUsuario'] ?? 'cliente';
            $nuevaPassword = $_POST['nuevaPassword'] ?? '';
            echo json_encode(modificarUsuario($nombre, $nuevoNombre, $nuevoCorreo, $nuevoTipoUsuario, $nuevaPassword));
        // CARGADORES
        } elseif (isset($_POST['agregar_cargador'])) {
            $nombre = $_POST['nombre'] ?? '';
            $latitud = $_POST['latitud'] ?? '';
            $longitud = $_POST['longitud'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $tipo = $_POST['tipo'] ?? '';
            $estado = $_POST['estado'] ?? 'disponible';
            $potencia_kw = $_POST['potencia_kw'] ?? 0;
            $conectores = $_POST['conectores'] ?? '';
            echo json_encode(agregarCargador($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores));
        } elseif (isset($_POST['modificar_cargador'])) {
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre'] ?? '';
            $latitud = $_POST['latitud'] ?? null;
            $longitud = $_POST['longitud'] ?? null;
            $descripcion = $_POST['descripcion'] ?? '';
            $tipo = $_POST['tipo'] ?? '';
            $estado = $_POST['estado'] ?? 'disponible';
            $potencia_kw = $_POST['potencia_kw'] ?? 0;
            $conectores = $_POST['conectores'] ?? '';
            
            // Si no se proporciona latitud/longitud, obtenerlos del registro actual
            if ($latitud === null || $longitud === null) {
                require_once __DIR__ . '/../modelo/Cargador.php';
                $cargadorModel = new Cargador();
                $cargadorActual = $cargadorModel->obtener($id);
                if ($cargadorActual) {
                    $latitud = $latitud ?? $cargadorActual['latitud'];
                    $longitud = $longitud ?? $cargadorActual['longitud'];
                }
            }
            
            echo json_encode(modificarCargador($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores));
        } elseif (isset($_POST['eliminar_cargador'])) {
            $id = $_POST['id'] ?? null;
            echo json_encode(eliminarCargador($id));
        // AUTOS
        } elseif (isset($data['accion']) && $data['accion'] === 'agregar_auto') {
            verificarAdmin();
            $usuario   = $data['usuario']   ?? '';
            $modelo    = $data['modelo']   ?? '';
            $marca     = $data['marca']    ?? '';
            $conector  = $data['conector'] ?? '';
            $autonomia = isset($data['autonomia']) ? (int)$data['autonomia'] : 0;
            $anio      = isset($data['anio']) ? (int)$data['anio'] : 0;

            echo json_encode(agregarAutoAdmin($usuario, $modelo, $marca, $conector, $autonomia, $anio));
        } elseif (isset($data['accion']) && $data['accion'] === 'eliminar_auto') {
            verificarAdmin();
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            echo json_encode(eliminarAutoAdmin($id));
        } elseif (isset($data['accion']) && $data['accion'] === 'modificar_auto') {
            verificarAdmin();
            $id        = isset($data['id']) ? (int)$data['id'] : 0;
            $marca     = $data['marca']    ?? '';
            $modelo    = $data['modelo']   ?? '';
            $conector  = $data['conector'] ?? '';
            $autonomia = isset($data['autonomia']) ? (int)$data['autonomia'] : 0;
            $anio      = isset($data['anio']) ? (int)$data['anio'] : 0;

            echo json_encode(editarAutoAdmin($id, $modelo, $marca, $conector, $autonomia, $anio));
        } else {
            echo json_encode(['error' => 'Acción POST no soportada']);
        }
        break;

    default:
        echo json_encode(['error' => 'Método no soportado']);
}
?>