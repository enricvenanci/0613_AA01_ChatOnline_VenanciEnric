<?php

// Para páginas privadas: si no hay sesión, se manda al login.
function exigir_login()
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../pages/login.php');
        exit();
    }
}

function exigir_admin($conexion)
{
    exigir_login();

    try {
        $consulta = $conexion->prepare('SELECT rol FROM usuarios WHERE id = :id');
        $consulta->execute(array(':id' => $_SESSION['usuario_id']));
    } catch (PDOException $excepcion) {
        if ($excepcion->getCode() != '42S22') {
            throw $excepcion;
        }

        $_SESSION['mensaje'] = 'Falta actualizar la base de datos.';
        header('Location: ../pages/chat.php');
        exit();
    }

    if ($consulta->fetchColumn() !== 'admin') {
        $_SESSION['mensaje'] = 'No tienes permiso para gestionar usuarios.';
        header('Location: ../pages/chat.php');
        exit();
    }
}

function validar_token($token)
{
    return isset($_SESSION['token'])
        && is_string($token)
        && hash_equals($_SESSION['token'], $token);
}

// Para login y registro: si ya hay sesión, se manda al chat.
function exigir_no_login()
{
    if (isset($_SESSION['usuario_id'])) {
        header('Location: ../pages/chat.php');
        exit();
    }
}
