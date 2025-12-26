<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tiendaZapatos');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');

function conectarDB() {
    try {
        $conexion = new PDO(
  "pgsql:host=localhost;port=5432;dbname=tiendaZapatos", "postgres", "postgres"
        );
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}