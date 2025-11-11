<?php

require_once __DIR__ . '/../modelo/Cargador.php';

// Funciones del controlador que pueden ser llamadas desde la API

function listarCargadores() {
    $cargadorModel = new Cargador();
    
    // Primero marcar reservas completadas
    require_once __DIR__ . '/ReservaControlador.php';
    ReservaControlador::marcarReservasCompletadas();
    
    // Luego liberar cargadores cuyas reservas ya finalizaron
    try {
        $cargadorModel->liberarCargadoresVencidos();
    } catch (Exception $e) {
        error_log("Error al liberar cargadores vencidos: " . $e->getMessage());
    }

    // Listado base
    $lista = $cargadorModel->listar();

    // Calcular estado en tiempo real y actualizar en BD si es necesario
    foreach ($lista as &$c) {
        // Respetar estados manuales (mantenimiento, fuera de servicio)
        $estadoActualBD = isset($c['estado']) ? strtolower($c['estado']) : 'disponible';
        if (in_array($estadoActualBD, ['mantenimiento', 'fuera de servicio'])) {
            continue;
        }
        
        // Verificar si tiene reserva activa AHORA
        $tieneReserva = $cargadorModel->tieneReservaActiva($c['id']);
        
        if ($tieneReserva) {
            // Debe estar ocupado
            $c['estado'] = 'ocupado';
            if ($estadoActualBD !== 'ocupado') {
                $cargadorModel->actualizarEstado($c['id'], 'ocupado');
            }
        } else {
            // Debe estar disponible
            $c['estado'] = 'disponible';
            if ($estadoActualBD === 'ocupado') {
                $cargadorModel->actualizarEstado($c['id'], 'disponible');
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