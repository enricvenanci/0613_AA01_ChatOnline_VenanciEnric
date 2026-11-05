<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/consultas/mensajes.php';

exigir_login();

$mi_id = $_SESSION['usuario_id'];
$adjunto_id = (int)($_GET['id'] ?? 0);

$archivo = false;
// busca el archuvo adjuntado  y mira si encuentra su id la funcion buscar adjunto hace que su id pueda ver si esta enviado en esa bd 
try {
    $archivo = buscar_adjunto($conexion, $adjunto_id);
} catch (PDOException $excepcion) {
    $archivo = false;
}
//si no ecuentra el archivo dentro de la careta manda el mensaje de Archivo no encontrado 
if (!$archivo || ($archivo['emisor_id'] != $mi_id && $archivo['receptor_id'] != $mi_id)) {
    echo 'Archivo no encontrado.';
    exit();
}

$ruta = '../uploads/' . basename($archivo['nombre_almacenado']);
// si el archivo existe pero el nombre no es el mismo error de archivo no encontrado
if (!file_exists($ruta)) {
    echo 'Archivo no encontrado.';
    exit();
}

header('Content-Type: ' . $archivo['mime_type']);
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($ruta));
//
if (substr($archivo['mime_type'], 0, 5) == 'image') {
    header('Content-Disposition: inline');
} else {
    $nombre_descarga = preg_replace('/[^A-Za-z0-9._-]/', '_', $archivo['nombre_original']);
    header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
}

session_write_close();

readfile($ruta);
exit();
?>