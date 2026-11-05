<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/validacion/usuario.php';
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
$username = trim($_POST['username'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';
$errores = array();

if ($usuario_id <= 0) {
    $errores[] = 'El usuario seleccionado no es válido.';
}

foreach (array(
    validar_username($username),
    validar_nombre($nombre),
    validar_email($email)
) as $error) {
    if ($error != '') {
        $errores[] = $error;
    }
}

if ($password != '' || $password2 != '') {
    $error = validar_password($password, $password2);
    if ($error != '') {
        $errores[] = $error;
    }
}

try {
    if ($usuario_id > 0 && username_existe_en_otro_usuario($conexion, $username, $usuario_id)) {
        $errores[] = 'Ese username ya está en uso.';
    }
    if ($usuario_id > 0 && email_existe_en_otro_usuario($conexion, $email, $usuario_id)) {
        $errores[] = 'Ese email ya está registrado.';
    }

    if (empty($errores)) {
        $actualizado = actualizar_usuario_admin(
            $conexion,
            $usuario_id,
            $username,
            $nombre,
            $email,
            $password
        );
        $_SESSION['mensaje_admin'] = $actualizado
            ? 'Datos del usuario actualizados.'
            : 'No se han detectado cambios en el usuario.';
    } else {
        $_SESSION['errores_admin'] = $errores;
    }
} catch (PDOException $excepcion) {
    $_SESSION['mensaje_admin'] = 'No se pudo actualizar el usuario. Comprueba que username y email no estén repetidos.';
}

header('Location: ' . $volver);
exit();
