<?php
require_once __DIR__ . '/../modelo/Viaje.php';

// Funciones del controlador que pueden ser llamadas desde la API

function agregarViaje($usuario, $origen, $destino, $fecha, $distancia_km = 0, $observaciones = null) {
    if (empty($usuario) || empty($origen) || empty($destino) || empty($fecha)) {
        return ['exito' => false, 'mensaje' => 'Faltan datos requeridos'];
    }
    $viajeModel = new Viaje();
    $ok = $viajeModel->insertar($usuario, $origen, $destino, $fecha, $distancia_km, $observaciones);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Viaje registrado' : 'Error al registrar viaje'];
}

function listarViajesUsuario($usuario) {
    if (empty($usuario)) {
        return [];
    }
    $viajeModel = new Viaje();
    return $viajeModel->listarPorUsuario($usuario);
}
?>