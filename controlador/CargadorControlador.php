<?php

require_once __DIR__ . '/../modelo/Cargador.php';

// Funciones del controlador que pueden ser llamadas desde la API

function listarCargadores() {
    $cargadorModel = new Cargador();
    return $cargadorModel->listar();
}

function agregarCargador($nombre, $latitud, $longitud, $descripcion = '', $tipo = '', $estado = 'disponible', $potencia_kw = 0, $conectores = '') {
    if (empty($nombre) || empty($latitud) || empty($longitud)) {
        return ['exito' => false, 'mensaje' => 'Datos incompletos'];
    }
    $cargadorModel = new Cargador();
    $ok = $cargadorModel->insertar($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Cargador agregado' : 'No se pudo agregar'];
}

function modificarCargador($id, $nombre, $latitud, $longitud, $descripcion = '', $tipo = '', $estado = 'disponible', $potencia_kw = 0, $conectores = '') {
    if (empty($id) || empty($nombre) || empty($latitud) || empty($longitud)) {
        return ['exito' => false, 'mensaje' => 'Datos incompletos'];
    }
    $cargadorModel = new Cargador();
    $ok = $cargadorModel->modificar($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Cargador modificado' : 'No se pudo modificar'];
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