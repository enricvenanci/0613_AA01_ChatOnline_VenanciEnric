
<?php
session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/consultas/amistades.php';

exigir_login();
//coge la variable buscar de amistades
$buscar = trim($_POST['buscar'] ?? '');
//pone en la variable volver la ruta de el chat con la url para que busque las solicitudes
$volver = '../pages/chat.php?seccion=buscar&buscar=' . urlencode($buscar);

//sesion si no vuelve a la pantalla que hay en volver
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ' . $volver);
    exit();
}

$mi_id = $_SESSION['usuario_id'];
$destino_id = (int)($_POST['usuario_id'] ?? 0);

//aqui busca las solicitudes que tenga el user y encuentra la solicitud junto al user la id y el destino
try {
    $mensaje = enviar_solicitud($conexion, $mi_id, $destino_id);

} catch (PDOException $excepcion) {
    $mensaje = 'Ha ocurrido un error al enviar la solicitud.';
}

$_SESSION['mensaje'] = $mensaje;
header('Location: ' . $volver);
exit();

?>