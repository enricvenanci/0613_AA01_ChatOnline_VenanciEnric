<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/validacion/usuario.php';
require_once '../src/consultas/usuarios.php';

exigir_no_login();

$errores = array();
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errores = validar_login($username, $password);

    if (empty($errores)) {

        
        try {
            //busca al user para poder guardarlo en la bd y asi poder 
            $usuario = buscar_usuario($conexion, $username);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {

                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['rol'] = $usuario['rol'];
                //actualiza la ultima vez que entraste a este user 
                actualizar_ultima_conexion($conexion, $usuario['id']);

                header('Location: chat.php');
                exit();

            } else {
                $errores[] = 'Username o contraseña incorrectos.';
            }

        } catch (PDOException $excepcion) {
            if ($excepcion->getCode() == '42S22') {
                $errores[] = 'Falta actualizar la base de datos. Ejecuta migrations/001_add_user_role.sql desde phpMyAdmin.';
            } else {
                $errores[] = 'Ha ocurrido un error. Inténtalo de nuevo.';
            }
        }
    }
}

// Datos que necesita la vista
$_SESSION['errores'] = $errores;
$_SESSION['form_username'] = $username;

require '../vistas/login.php';
