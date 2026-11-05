<?php

session_start();

require_once '../config/conexion.php';
require_once '../src/sesion.php';
require_once '../src/validacion/mensaje.php';
require_once '../src/consultas/amistades.php';
require_once '../src/consultas/mensajes.php';

// esto envia a /src/amistades.php aqui se encuentran todas las funciones de session
exigir_login(); 

//esto hace que compruebe que se esta enviando por metodo post si no te envia a el chat
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../pages/chat.php');
    exit();
}

$mi_id = $_SESSION['usuario_id'];
$amigo_id = (int)($_POST['amigo_id'] ?? 0);
$contenido = trim($_POST['contenido'] ?? '');

$error = '';
$tipo = 'texto';
$ruta_destino = '';
$nombre_original = '';
$nombre_guardado = '';
$mime = '';
$tamano = 0;

try {

    //Si no son amigos entre las dos personas te dice que no tienes acceso a ese chat ya que comprueba si el campo de la bd coinciden y son amigos 
    if (!son_amigos($conexion, $mi_id, $amigo_id)) {
        $_SESSION['mensaje'] = 'No tienes acceso a ese chat.';
        header('Location: ../pages/chat.php');
        exit();
    }

    // se pregunta al user ¿el usuario ha adjuntado un archivo en el formulario?”. Guarda true o false en $hay_archivo.
    $hay_archivo = isset($_FILES['archivo']) && $_FILES['archivo']['error'] != UPLOAD_ERR_NO_FILE;

    $error = validar_texto_mensaje($contenido, $hay_archivo);

    //si el error esta vacio y la variable hay archivo es true que entre en el if
    if ($error == '' && $hay_archivo) {


        $archivo = $_FILES['archivo'];

        $resultado = validar_archivo($archivo);
        $error = $resultado['error'];

        if ($error == '') {

            $tipo = $resultado['tipo'];
            $mime = $resultado['mime'];

            //se redirige a la carpeta uploads donde se encuentran los archivos y las imagenes
            $carpeta = '../uploads/';

            // randomiza el nombre del archivo para que este tenga 16
            $nombre_guardado = bin2hex(random_bytes(16)) . '.' . $resultado['extension'];

            //aqui se guarda la ruta de la carpeta
            $ruta_destino = $carpeta . $nombre_guardado;

            //comprueba si el archivo esta en su ruta y le cambia a su nombre original para 
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                $nombre_original = mb_substr(basename($archivo['name']), 0, 255);
                $tamano = (int)$archivo['size'];
            } else {
                $error = 'No se ha podido guardar el archivo.';
                $ruta_destino = '';
                $nombre_guardado = '';
            }
        }
    }

    if ($error == '') {
        guardar_mensaje($conexion, $mi_id, $amigo_id, $tipo, $contenido, $nombre_original, $nombre_guardado, $mime, $tamano);
    }

    //si no consigue hacer el try se mete en el catch y aqui mira si es por error o es por 
} catch (PDOException $excepcion) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    
    if ($ruta_destino != '' && file_exists($ruta_destino)) {
        unlink($ruta_destino);
    }
    $error = 'Ha ocurrido un error al enviar el mensaje.';
}

if ($error != '') {
    $_SESSION['mensaje'] = $error;
}

header('Location: ../pages/conversacion.php?amigo=' . $amigo_id . '#final');
exit();
?>