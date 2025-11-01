<?php

require_once __DIR__ . '/../modelo/Cargador.php';

// Funciones del controlador que pueden ser llamadas desde la API

function listarCargadores() {
    $cargadorModel = new Cargador();
    return $cargadorModel->listar();
}

function agregarCargador($nombre, $latitud, $longitud) {
    if (empty($nombre) || empty($latitud) || empty($longitud)) {
        return ['exito' => false, 'mensaje' => 'Datos incompletos'];
    }
    $cargadorModel = new Cargador();
    // Modelo Cargador espera 4 parámetros, pero solo usamos 3, así que pasamos '' para descripcion
    $ok = $cargadorModel->insertar($nombre, $latitud, $longitud, '');
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Cargador agregado' : 'No se pudo agregar'];
}

function eliminarCargador($id) {
    if (empty($id)) {
        return ['exito' => false, 'mensaje' => 'ID no recibido'];
    }
    $cargadorModel = new Cargador();
    $ok = $cargadorModel->eliminar($id);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Cargador eliminado' : 'No se pudo eliminar'];
}
?>