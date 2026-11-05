<?php

function marcar_como_leidos($conexion, $mi_id, $amigo_id)
{
    $sql = 'UPDATE mensajes SET leido = 1
            WHERE emisor_id = :amigo AND receptor_id = :yo AND leido = 0';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':amigo', $amigo_id);
    $consulta->bindParam(':yo', $mi_id);
    $consulta->execute();
}
//lee la lista de mensajes y los pone en ordenen en el chat 
function listar_mensajes($conexion, $mi_id, $amigo_id)
{
    $sql = 'SELECT m.id, m.emisor_id, m.tipo, m.contenido, m.fecha_envio,
                   u.username,
                   ad.id AS adjunto_id, ad.nombre_original
            FROM mensajes m
            JOIN usuarios u ON u.id = m.emisor_id
            LEFT JOIN adjuntos ad ON ad.mensaje_id = m.id
            WHERE (m.emisor_id = :yo1 AND m.receptor_id = :amigo1)
               OR (m.emisor_id = :amigo2 AND m.receptor_id = :yo2)
            ORDER BY m.fecha_envio ASC, m.id ASC';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo1', $mi_id);
    $consulta->bindParam(':amigo1', $amigo_id);
    $consulta->bindParam(':amigo2', $amigo_id);
    $consulta->bindParam(':yo2', $mi_id);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

// Guarda el mensaje y, si hay archivo, también su adjunto (todo junto en una transacción).
// Si no hay archivo, $nombre_original, $nombre_guardado, $mime y $tamano van vacíos.
function guardar_mensaje($conexion, $mi_id, $amigo_id, $tipo, $contenido, $nombre_original, $nombre_guardado, $mime, $tamano)
{
    $conexion->beginTransaction();

    $sql = 'INSERT INTO mensajes (emisor_id, receptor_id, tipo, contenido)
            VALUES (:yo, :amigo, :tipo, :contenido)';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo', $mi_id);
    $consulta->bindParam(':amigo', $amigo_id);
    $consulta->bindParam(':tipo', $tipo);
    if ($contenido == '') {
        $contenido_bd = null;
    } else {
        $contenido_bd = $contenido;
    }
    $consulta->bindParam(':contenido', $contenido_bd);
    $consulta->execute();

    if ($nombre_guardado != '') {
        $mensaje_id = $conexion->lastInsertId();

        $sql = 'INSERT INTO adjuntos (mensaje_id, nombre_original, nombre_almacenado, mime_type, tamano_bytes)
                VALUES (:mensaje_id, :original, :almacenado, :mime, :tamano)';
        $consulta = $conexion->prepare($sql);
        $consulta->bindParam(':mensaje_id', $mensaje_id);
        $consulta->bindParam(':original', $nombre_original);
        $consulta->bindParam(':almacenado', $nombre_guardado);
        $consulta->bindParam(':mime', $mime);
        $consulta->bindParam(':tamano', $tamano);
        $consulta->execute();
    }

    $conexion->commit();
}

// Devuelve el adjunto con el emisor y receptor de su mensaje, o false si no existe.
function buscar_adjunto($conexion, $adjunto_id)
{
    $sql = 'SELECT ad.nombre_original, ad.nombre_almacenado, ad.mime_type,
                   m.emisor_id, m.receptor_id
            FROM adjuntos ad
            JOIN mensajes m ON m.id = ad.mensaje_id
            WHERE ad.id = :id';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':id', $adjunto_id);
    $consulta->execute();

    return $consulta->fetch(PDO::FETCH_ASSOC);
}
