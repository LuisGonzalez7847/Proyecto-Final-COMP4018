<?php
// Conexion a la base de datos nba_db en MySQL via XAMPP

$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$basedatos = 'nba_db';

$conn = new mysqli($host, $usuario, $contrasena, $basedatos);

if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>