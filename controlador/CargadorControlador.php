<?php

require_once __DIR__ . '/../modelo/Cargador.php';

// Funciones del controlador que pueden ser llamadas desde la API

function listarCargadores() {
    $cargadorModel = new Cargador();
    // Liberar cargadores cuyas reservas ya finalizaron antes de listar
    try {
        $cargadorModel->liberarCargadoresVencidos();
    } catch (Exception $e) {
        // Si falla la liberación, continuamos con el listado
        error_log("Error al liberar cargadores vencidos: " . $e->getMessage());
    }

    // Listado base
    $lista = $cargadorModel->listar();

    // Calcular estado en tiempo real: si tiene reserva ACTIVA ahora => "ocupado"
    $ahora = date('Y-m-d H:i:s');
    foreach ($lista as &$c) {
        // Respetar estados manuales
        $estado = isset($c['estado']) ? strtolower($c['estado']) : 'disponible';
        if (in_array($estado, ['mantenimiento', 'fuera de servicio'])) {
            continue;
        }
        // Si hay una reserva activa ahora, marcar ocupado en la respuesta
        if ($cargadorModel->tieneReservaActiva($c['id'])) {
            $c['estado'] = 'ocupado';
        } else {
            // Si no hay reserva activa y estaba "ocupado", mostrar "disponible" en respuesta
            // (la liberación en BD puede depender del cron/listado previo)
            if ($estado === 'ocupado') {
                $c['estado'] = 'disponible';
            }
        }
    }
    unset($c);

    return $lista;
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