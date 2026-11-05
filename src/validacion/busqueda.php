<?php

// Devuelve el error de la búsqueda, o '' si está bien.
function validar_busqueda($texto)
{
    if ($texto == '') {
        return 'Escribe un username o un nombre para buscar.';
    }
    if (mb_strlen($texto) > 100) {
        return 'La búsqueda no puede tener más de 100 caracteres.';
    }
    return '';
}
