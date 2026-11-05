<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/validacion/usuario.php';
require_once '../src/consultas/usuarios.php';

exigir_no_login();

$errores = array();

$username = '';
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    $errores = validar_registro($username, $nombre, $email, $password, $password2);

    if (empty($errores)) {

        try {

            if (username_existe($conexion, $username)) {
                $errores[] = 'Ese username ya está en uso.';
            }

            if (email_existe($conexion, $email)) {
                $errores[] = 'Ese email ya está registrado.';
            }

            if (empty($errores)) {

                crear_usuario($conexion, $username, $nombre, $email, $password);

                $_SESSION['mensaje'] = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';

                header('Location: login.php');
                exit();
            }

        } catch (PDOException $excepcion) {
            // 1062 = clave duplicada: otro usuario se registró con el mismo username/email
            // justo entre las comprobaciones y el INSERT.
            if (isset($excepcion->errorInfo[1]) && $excepcion->errorInfo[1] == 1062) {
                $errores[] = 'Ese username o ese email ya están registrados.';
            } else {
                $errores[] = 'Ha ocurrido un error al guardar los datos. Inténtalo de nuevo.';
            }
        }
    }
}

// Datos que necesita la vista
$_SESSION['errores'] = $errores;
$_SESSION['form_username'] = $username;
$_SESSION['form_nombre'] = $nombre;
$_SESSION['form_email'] = $email;

require '../vistas/register.php';
