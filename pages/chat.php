<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/validacion/busqueda.php';
require_once '../src/consultas/amistades.php';

exigir_login();

$mi_id = $_SESSION['usuario_id'];

// Sección que se muestra: amigos, solicitudes o buscar
$seccion = $_GET['seccion'] ?? 'amigos';
if ($seccion != 'amigos' && $seccion != 'solicitudes' && $seccion != 'buscar') {
    $seccion = 'amigos';
}

$buscar = trim($_GET['buscar'] ?? '');
$buscado = isset($_GET['buscar']);

$errores = array();
$amigos = array();
$solicitudes = array();
$resultados = array();
//lanzas una peticion de cosas con una respuesta si no las hace
try {
    //aqui lista todas las solicitudes de amistad recibidas
    $solicitudes = listar_solicitudes_recibidas($conexion, $mi_id);
    //aqui hace un listado de los amigos que hay 
    if ($seccion == 'amigos') {
        $amigos = listar_amigos($conexion, $mi_id);
    }
    
    if ($seccion == 'buscar' && $buscado) {
        //la validacion de busqueda entra en esta funcion y la valida con las dos restricciones que hay 
        $error = validar_busqueda($buscar);

        if ($error != '') {
            $errores[] = $error;
        } else 
        
        {//recoge lo que hayas añadido en la busqueda de amigos y con la validacion de busqueda recoge los amigos que haya con la caracterisitica que hayas puesto
            $resultados = buscar_usuarios($conexion, $mi_id, $buscar);
        }
    }

    //si hace el try devuelve un error de carga de los datos 
} catch (PDOException $excepcion) {
    $errores[] = 'Ha ocurrido un error al cargar los datos. Inténtalo de nuevo.';
}

// Datos que necesita la vista
$_SESSION['seccion'] = $seccion;
$_SESSION['buscar'] = $buscar;
$_SESSION['buscado'] = $buscado;
$_SESSION['errores'] = $errores;
$_SESSION['amigos'] = $amigos;
$_SESSION['solicitudes'] = $solicitudes;
$_SESSION['resultados'] = $resultados;

require '../vistas/chat.php';
