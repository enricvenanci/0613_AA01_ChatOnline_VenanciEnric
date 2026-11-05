<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/consultas/usuarios.php';

exigir_admin($conexion);

$buscar = trim($_POST['buscar'] ?? '');
$volver = '../pages/admin.php?buscar=' . urlencode($buscar);

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !validar_token($_POST['token'] ?? '')) {
    $_SESSION['mensaje_admin'] = 'La solicitud no es válida. Vuelve a intentarlo.';
    header('Location: ' . $volver);
    exit();
}

$usuario_id = (int)($_POST['usuario_id'] ?? 0);

try {
    $_SESSION['mensaje_admin'] = eliminar_usuario_admin(
        $conexion,
        $usuario_id,
        (int)$_SESSION['usuario_id']
    );
} catch (PDOException $excepcion) {
    $_SESSION['mensaje_admin'] = 'No se pudo borrar el usuario. Inténtalo de nuevo.';
}

header('Location: ' . $volver);
exit();
