<?php
$host = "127.0.0.1";
$usuario = "root";
$clave ="";
$bd = "registro_usuarios";
$puerto = "3307";

$conn = new mysqli($host, $usuario, $clave, $bd, $puerto);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
    exit();
}

$conn->set_charset("utf8mb4");
?>