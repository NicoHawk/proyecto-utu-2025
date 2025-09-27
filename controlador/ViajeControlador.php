<?php
session_start();
require_once __DIR__ . '/../modelo/Viaje.php';

header('Content-Type: application/json');

$viajeModel = new Viaje();

function agregarViaje($viajeModel) {
    $usuario = $_SESSION['usuario'] ?? '';
    $origen = $_POST['origen'] ?? '';
    $destino = $_POST['destino'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d H:i:s');
    $distancia_km = $_POST['distancia_km'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? null;

    if ($usuario && $origen && $destino && $fecha) {
        $ok = $viajeModel->insertar($usuario, $origen, $destino, $fecha, $distancia_km, $observaciones);
        echo json_encode(['success' => $ok, 'mensaje' => $ok ? 'Viaje registrado' : 'Error al registrar viaje']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos']);
    }
}

function listarViajes($viajeModel) {
    $usuario = $_SESSION['usuario'] ?? '';
    if ($usuario) {
        $viajes = $viajeModel->listarPorUsuario($usuario);
        echo json_encode($viajes);
    } else {
        echo json_encode([]);
    }
}

function accionNoValida() {
    echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
switch ($accion) {
    case 'agregar':
        agregarViaje($viajeModel);
        break;
    case 'listar':
        listarViajes($viajeModel);
        break;
    default:
        accionNoValida();
        break;
}
?>