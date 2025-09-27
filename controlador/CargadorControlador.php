<?php

// SOLO FUNCIONES, sin ejecución automática ni salida JSON
function listarCargadores($conn) {
    $sql = "SELECT * FROM cargadores";
    $result = $conn->query($sql);
    $cargadores = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $cargadores[] = $row;
    }
    return $cargadores;
}

function agregarCargador($conn, $nombre, $latitud, $longitud) {
    $stmt = $conn->prepare("INSERT INTO cargadores (nombre, latitud, longitud) VALUES (?, ?, ?)");
    return $stmt->execute([$nombre, $latitud, $longitud]);
}

function eliminarCargador($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM cargadores WHERE id = ?");
    return $stmt->execute([$id]);
}
?>