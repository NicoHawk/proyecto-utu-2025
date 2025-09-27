<?php

// SOLO FUNCIONES, sin ejecución automática ni salida JSON
function listarCargadores($conn) {
    $sql = "SELECT * FROM cargadores";
    $result = $conn->query($sql);
    $cargadores = [];
    while($row = $result->fetch_assoc()) {
        $cargadores[] = $row;
    }
    return $cargadores;
}

function agregarCargador($conn, $nombre, $latitud, $longitud) {
    $stmt = $conn->prepare("INSERT INTO cargadores (nombre, latitud, longitud) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $nombre, $latitud, $longitud);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

function eliminarCargador($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM cargadores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}
?>