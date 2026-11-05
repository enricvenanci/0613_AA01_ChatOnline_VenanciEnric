<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/consultas/amistades.php';
require_once '../src/consultas/mensajes.php';

exigir_login();

$mi_id = $_SESSION['usuario_id'];
$amigo_id = (int)($_GET['amigo'] ?? 0);
//recoge a el amigo que has seleccionado y lo recoge en una variable para luego poder enviarlo a la bd
try {

    $amigo = buscar_amigo($conexion, $mi_id, $amigo_id);

    //mira el amigo que has seleccionado y si no es ese te redirige a chat.php
    if (!$amigo) {
        $_SESSION['mensaje'] = 'No tienes acceso a ese chat.';
        header('Location: chat.php');
        exit();
    }
    //mira si el mensaje lo ha leido la otra persona
    marcar_como_leidos($conexion, $mi_id, $amigo_id);
    //hace una lista de cada mensaje y los pone por orden
    $mensajes = listar_mensajes($conexion, $mi_id, $amigo_id);
//si no conisgue hacer el try acaba con su catch
} catch (PDOException $excepcion) {
    $_SESSION['mensaje'] = 'Ha ocurrido un error al abrir el chat.';
    header('Location: chat.php');
    exit();
}

// Datos que necesita la vista
$_SESSION['amigo'] = $amigo;
$_SESSION['mensajes'] = $mensajes;

require '../vistas/conversacion.php';
