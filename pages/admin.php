<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/validacion/busqueda.php';
require_once '../src/consultas/usuarios.php';

exigir_admin($conexion);

$mi_id = $_SESSION['usuario_id'];
$buscar = trim($_GET['buscar'] ?? '');
$buscado = isset($_GET['buscar']);
$errores = array();
$resultados = array();
$mensaje = $_SESSION['mensaje_admin'] ?? '';
unset($_SESSION['mensaje_admin']);
$errores = $_SESSION['errores_admin'] ?? array();
unset($_SESSION['errores_admin']);

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

try {
    if ($buscado) {
        $error = validar_busqueda($buscar);
        if ($error != '') {
            $errores[] = $error;
        } else {
            $resultados = buscar_usuarios_admin($conexion, $buscar);
        }
    }
} catch (PDOException $excepcion) {
    $errores[] = 'Ha ocurrido un error al buscar usuarios. Inténtalo de nuevo.';
}

$_SESSION['admin_busqueda'] = array(
    'buscar' => $buscar,
    'buscado' => $buscado,
    'errores' => $errores,
    'resultados' => $resultados,
    'mensaje' => $mensaje,
    'token' => $_SESSION['token']
);

require '../vistas/admin.php';
