<?php

// Devuelve el error del texto del mensaje, o '' si está bien.
function validar_texto_mensaje($contenido, $hay_archivo)
{
    if (mb_strlen($contenido) > 250) {
        return 'El mensaje no puede tener más de 250 caracteres.';
    }
    if ($contenido == '' && !$hay_archivo) {
        return 'Escribe un mensaje o adjunta un archivo.';
    }
    return '';
}

// Comprueba el archivo subido. Devuelve un array con:
// 'error' (vacío si todo está bien), 'extension', 'mime' y 'tipo' ('imagen' o 'archivo').
function validar_archivo($archivo)
{
    $resultado = array('error' => '', 'extension' => '', 'mime' => '', 'tipo' => '');

    // Tipos permitidos: extensión => tipo MIME real y tipo de mensaje
    $permitidos = array(
        'jpg'  => array('image/jpeg',      'imagen'),
        'jpeg' => array('image/jpeg',      'imagen'),
        'png'  => array('image/png',       'imagen'),
        'gif'  => array('image/gif',       'imagen'),
        'pdf'  => array('application/pdf', 'archivo'),
        'txt'  => array('text/plain',      'archivo'),
    );
    $max_bytes = 5 * 1024 * 1024; // 5 MB

    if ($archivo['error'] == UPLOAD_ERR_INI_SIZE || $archivo['error'] == UPLOAD_ERR_FORM_SIZE || $archivo['size'] > $max_bytes) {
        $resultado['error'] = 'El archivo es demasiado grande (máximo 5 MB).';
        return $resultado;
    }

    if ($archivo['error'] != UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
        $resultado['error'] = 'No se ha podido subir el archivo.';
        return $resultado;
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

    // El tipo se comprueba por el contenido real del archivo, no por lo que diga el navegador.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    if (!isset($permitidos[$extension]) || $permitidos[$extension][0] != $mime) {
        $resultado['error'] = 'Tipo de archivo no permitido. Solo jpg, png, gif, pdf y txt.';
        return $resultado;
    }

    $resultado['extension'] = $extension;
    $resultado['mime'] = $mime;
    $resultado['tipo'] = $permitidos[$extension][1];
    return $resultado;
}
