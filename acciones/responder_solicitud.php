<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/consultas/amistades.php';

exigir_login();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../pages/chat.php?seccion=solicitudes');
    exit();
}

$mi_id = $_SESSION['usuario_id'];
$solicitud_id = (int)($_POST['solicitud_id'] ?? 0);
$accion = $_POST['accion'] ?? '';

$mensaje = '';
//dependiedno de la opcion que le pulses se guarda en la variable.
if ($accion == 'aceptar') {
    $nuevo_estado = 'aceptada';
} elseif ($accion == 'rechazar') {
    $nuevo_estado = 'rechazada';
} else {
    $nuevo_estado = '';
}
//si la id de la solicitud es 0 de que es un error y que no es valida
if ($solicitud_id <= 0 || $nuevo_estado == '') {
    $mensaje = 'Acción no válida.';
} else {

    try {
        //dependiendo de la respuesta muestra el mesnaje de aceptada o no. 
        $respondida = responder_solicitud($conexion, $solicitud_id, $mi_id, $nuevo_estado);

        if (!$respondida) {
            $mensaje = 'No se ha podido responder a esa solicitud.';
        } elseif ($nuevo_estado == 'aceptada') {
            $mensaje = 'Solicitud aceptada. ¡Ya sois amigos!';
        } else {
            $mensaje = 'Solicitud rechazada.';
        }
    //si el servidor no lo consigue lo rechaza y te da error
    } catch (PDOException $excepcion) {
        $mensaje = 'Ha ocurrido un error al responder la solicitud.';
    }
}

$_SESSION['mensaje'] = $mensaje;
header('Location: ../pages/chat.php?seccion=solicitudes');
exit();
?>