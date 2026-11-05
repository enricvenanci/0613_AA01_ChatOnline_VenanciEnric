<?php

// Todas las funciones devuelven un texto con el error, o '' si todo está bien.

function validar_username($username)
{
    if ($username == '') {
        return 'El username es obligatorio.';
    }
    if (strlen($username) < 3 || strlen($username) > 30) {
        return 'El username debe tener entre 3 y 30 caracteres.';
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        return 'El username solo puede tener letras (sin tildes), números y guion bajo (_).';
    }
    return '';
}

function validar_nombre($nombre)
{
    if ($nombre == '') {
        return 'El nombre real es obligatorio.';
    }
    if (mb_strlen($nombre) > 100) {
        return 'El nombre real no puede tener más de 100 caracteres.';
    }
    return '';
}

function validar_email($email)
{
    if ($email == '') {
        return 'El email es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'El email no es válido.';
    }
    if (strlen($email) > 150) {
        return 'El email no puede tener más de 150 caracteres.';
    }
    return '';
}

function validar_password($password, $password2)
{
    if ($password == '') {
        return 'La contraseña es obligatoria.';
    }
    if (strlen($password) < 8) {
        return 'La contraseña debe tener al menos 8 caracteres.';
    }
    if (strlen($password) > 72) {
        return 'La contraseña no puede tener más de 72 caracteres.';
    }
    if ($password != $password2) {
        return 'Las dos contraseñas no coinciden.';
    }
    return '';
}

// Junta los errores de todos los campos en una lista (vacía si todo está bien).
function validar_registro($username, $nombre, $email, $password, $password2)
{
    $errores = array();

    $error = validar_username($username);
    if ($error != '') {
        $errores[] = $error;
    }

    $error = validar_nombre($nombre);
    if ($error != '') {
        $errores[] = $error;
    }

    $error = validar_email($email);
    if ($error != '') {
        $errores[] = $error;
    }

    $error = validar_password($password, $password2);
    if ($error != '') {
        $errores[] = $error;
    }

    return $errores;
}

function validar_login($username, $password)
{
    $errores = array();

    if ($username == '' || $password == '') {
        $errores[] = 'Escribe tu username y tu contraseña.';
    }

    return $errores;
}
