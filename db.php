<?php
// filepath: c:\xampp\htdocs\proyecto_new1\db.php
// Zona horaria fija para toda la app (evita desalineaciones si php.ini trae otra distinta)
date_default_timezone_set('America/Montevideo');

function conectar() {
    try {
        $conexion = new PDO('mysql:host=localhost;dbname=gestion_db', 'root', 'root');
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Alinear la zona horaria de la sesión MySQL (por si se usa NOW() u operaciones de fecha del lado SQL)
        // Nota: Uruguay no usa DST actualmente, offset estable -03:00
        try { $conexion->exec("SET time_zone = '-03:00'"); } catch (Exception $e) { /* opcional */ }
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
?>
