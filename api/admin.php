<?php

require_once __DIR__ . '/../controlador/UsuarioControlador.php';
require_once __DIR__ . '/../controlador/CargadorControlador.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'OPTIONS':
        http_response_code(200);
        exit;

    case 'GET':
        if (isset($_GET['listar_usuarios'])) {
            echo json_encode(listarUsuarios());
        } elseif (isset($_GET['listar_cargadores'])) {
            $conn = getCargadorConn();
            $cargadores = listarCargadores($conn);
            $conn->close();
            echo json_encode($cargadores);
        } else {
            echo json_encode(['error' => 'Acción GET no soportada']);
        }
        break;

    case 'POST':
        // USUARIOS
        if (isset($_POST['agregar_usuario'])) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $tipo_usuario = $_POST['tipo_usuario'] ?? 'cliente';
            echo json_encode(registrarUsuario($username, $password, $tipo_usuario));
        } elseif (isset($_POST['eliminar_usuario'])) {
            $nombre = $_POST['nombre'] ?? '';
            echo json_encode(eliminarUsuario($nombre));
        } elseif (isset($_POST['modificar_usuario'])) {
            $nombre = $_POST['nombre'] ?? '';
            $nuevoNombre = $_POST['nuevoNombre'] ?? '';
            $nuevoTipoUsuario = $_POST['nuevoTipoUsuario'] ?? 'cliente';
            $nuevaPassword = $_POST['nuevaPassword'] ?? '';
            echo json_encode(modificarUsuario($nombre, $nuevoNombre, $nuevoTipoUsuario, $nuevaPassword));
        // CARGADORES
        } elseif (isset($_POST['agregar_cargador'])) {
            $nombre = $_POST['nombre'] ?? '';
            $latitud = $_POST['latitud'] ?? '';
            $longitud = $_POST['longitud'] ?? '';
            $conn = getCargadorConn();
            if ($nombre === '' || $latitud === '' || $longitud === '') {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
                $conn->close();
                exit;
            }
            $stmt = $conn->prepare("INSERT INTO cargadores (nombre, latitud, longitud) VALUES (?, ?, ?)");
            $stmt->bind_param("sdd", $nombre, $latitud, $longitud);
            if ($stmt->execute()) {
                echo json_encode(['exito' => true]);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo agregar']);
            }
            $stmt->close();
            $conn->close();
        } elseif (isset($_POST['eliminar_cargador'])) {
            $id = $_POST['id'] ?? null;
            $conn = getCargadorConn();
            if ($id === null) {
                echo json_encode(['exito' => false, 'mensaje' => 'ID no recibido']);
                $conn->close();
                exit;
            }
            $id = intval($id);
            $stmt = $conn->prepare("DELETE FROM cargadores WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(['exito' => true, 'mensaje' => 'Cargador eliminado']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo eliminar el cargador']);
            }
            $stmt->close();
            $conn->close();
        } else {
            echo json_encode(['error' => 'Acción POST no soportada']);
        }
        break;

    default:
        echo json_encode(['error' => 'Método no soportado']);
}

// Función para obtener conexión a la base de datos de cargadores
function getCargadorConn() {
    $host = "localhost";
    $user = "root";
    $pass = "root";
    $db = "gestion_db";
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        echo json_encode(['exito' => false, 'mensaje' => 'Error de conexión a la base de datos']);
        exit;
    }
    return $conn;
}
