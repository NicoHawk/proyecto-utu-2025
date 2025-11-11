<?php
require_once __DIR__ . '/../modelo/Cargador.php';

// Funciones del controlador que pueden ser llamadas desde la API

function listarCargadores() {
    $cargador = new Cargador();
    return $cargador->listar();
}

function agregarCargador($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores) {
    $cargador = new Cargador();
    return $cargador->crear($nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores);
}

function modificarCargador($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores) {
    $cargador = new Cargador();
    return $cargador->modificar($id, $nombre, $latitud, $longitud, $descripcion, $tipo, $estado, $potencia_kw, $conectores);
}

function eliminarCargador($id) {
    $cargador = new Cargador();
    return $cargador->eliminar($id);
}
?>