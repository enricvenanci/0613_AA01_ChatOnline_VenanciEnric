<?php

$servidor = "localhost";
$puerto = 3306;            
$usuario_bd = "root";
$password_bd = "";
$nombre_bd = "chatonline";

try {

    $conexion = new PDO("mysql:host=$servidor;port=$puerto;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $excepcion) {
    error_log('Error de conexión a la base de datos: ' . $excepcion->getMessage());
   }
