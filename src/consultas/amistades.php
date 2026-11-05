<?php

// Devuelve los datos del amigo, o false si no son amigos.
function buscar_amigo($conexion, $mi_id, $amigo_id)
{
    $sql = 'SELECT u.id, u.username, u.nombre_real
            FROM vista_amigos va
            JOIN usuarios u ON u.id = va.amigo_id
            WHERE va.usuario_id = :yo AND va.amigo_id = :amigo';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo', $mi_id);
    $consulta->bindParam(':amigo', $amigo_id);
    $consulta->execute();

    return $consulta->fetch(PDO::FETCH_ASSOC);
}

function son_amigos($conexion, $mi_id, $amigo_id)
{
    if ($amigo_id <= 0) {
        return false;
    }

    if (buscar_amigo($conexion, $mi_id, $amigo_id)) {
        return true;
    }
    return false;
}

// Devuelve true si la solicitud pendiente existía y se ha respondido.
function responder_solicitud($conexion, $solicitud_id, $mi_id, $nuevo_estado)
{
    $sql = "UPDATE amistades
            SET estado = :estado, fecha_respuesta = CURRENT_TIMESTAMP
            WHERE id = :id AND destinatario_id = :yo AND estado = 'pendiente'";
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':estado', $nuevo_estado);
    $consulta->bindParam(':id', $solicitud_id);
    $consulta->bindParam(':yo', $mi_id);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        return true;
    }
    return false;
}

// Lista de amigos con el número de mensajes que aún no he leído de cada uno.
function listar_amigos($conexion, $mi_id)
{
    $sql = 'SELECT u.id, u.username, u.nombre_real,
                   (SELECT COUNT(*) FROM mensajes m
                    WHERE m.emisor_id = u.id AND m.receptor_id = :yo1 AND m.leido = 0) AS sin_leer
            FROM vista_amigos va
            JOIN usuarios u ON u.id = va.amigo_id
            WHERE va.usuario_id = :yo2
            ORDER BY u.username ASC';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo1', $mi_id);
    $consulta->bindParam(':yo2', $mi_id);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

// Solicitudes de amistad que me han enviado y están pendientes de responder.
function listar_solicitudes_recibidas($conexion, $mi_id)
{
    $sql = "SELECT a.id, u.username, u.nombre_real, a.fecha_solicitud
            FROM amistades a
            JOIN usuarios u ON u.id = a.solicitante_id
            WHERE a.destinatario_id = :yo AND a.estado = 'pendiente'
            ORDER BY a.fecha_solicitud DESC";
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo', $mi_id);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

// Busca usuarios (menos yo) cuyo username o nombre real contenga el texto.
// Con cada usuario devuelve la relación que ya tengo con él (estado y quién envió la solicitud).
function buscar_usuarios($conexion, $mi_id, $texto)
{
    // Se escapan % y _ para que se busquen como letras normales y no como comodines.
    $texto_escapado = addcslashes($texto, '%_' . chr(92));
    $patron = '%' . $texto_escapado . '%';

    $sql = 'SELECT u.id, u.username, u.nombre_real, a.estado, a.solicitante_id
            FROM usuarios u
            LEFT JOIN amistades a
                   ON (a.solicitante_id = u.id AND a.destinatario_id = :yo1)
                   OR (a.solicitante_id = :yo2 AND a.destinatario_id = u.id)
            WHERE u.id != :yo3
              AND (u.username LIKE :patron1 OR u.nombre_real LIKE :patron2)
            ORDER BY u.username ASC
            LIMIT 20';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo1', $mi_id);
    $consulta->bindParam(':yo2', $mi_id);
    $consulta->bindParam(':yo3', $mi_id);
    $consulta->bindParam(':patron1', $patron);
    $consulta->bindParam(':patron2', $patron);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

// Envía una solicitud de amistad. Devuelve un texto explicando qué ha pasado.
function enviar_solicitud($conexion, $mi_id, $destino_id)
{
    if ($destino_id <= 0 || $destino_id == $mi_id) {
        return 'Usuario no válido.';
    }

    // El usuario al que se envía tiene que existir
    $consulta = $conexion->prepare('SELECT id FROM usuarios WHERE id = :id');
    $consulta->bindParam(':id', $destino_id);
    $consulta->execute();
    if (!$consulta->fetch()) {
        return 'Ese usuario no existe.';
    }

    // ¿Ya hay alguna relación entre los dos (en cualquier sentido)?
    $sql = 'SELECT id, estado FROM amistades
            WHERE (solicitante_id = :yo1 AND destinatario_id = :otro1)
               OR (solicitante_id = :otro2 AND destinatario_id = :yo2)';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':yo1', $mi_id);
    $consulta->bindParam(':otro1', $destino_id);
    $consulta->bindParam(':otro2', $destino_id);
    $consulta->bindParam(':yo2', $mi_id);
    $consulta->execute();
    $relacion = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($relacion && $relacion['estado'] == 'aceptada') {
        return 'Ya sois amigos.';
    }
    if ($relacion && $relacion['estado'] == 'pendiente') {
        return 'Ya hay una solicitud pendiente entre vosotros.';
    }

    if ($relacion) {
        // Estaba rechazada: se reutiliza la fila y se vuelve a dejar pendiente
        $sql = "UPDATE amistades
                SET solicitante_id = :yo, destinatario_id = :otro, estado = 'pendiente',
                    fecha_solicitud = CURRENT_TIMESTAMP, fecha_respuesta = NULL
                WHERE id = :id";
        $consulta = $conexion->prepare($sql);
        $consulta->bindParam(':yo', $mi_id);
        $consulta->bindParam(':otro', $destino_id);
        $consulta->bindParam(':id', $relacion['id']);
        $consulta->execute();
    } else {
        $sql = 'INSERT INTO amistades (solicitante_id, destinatario_id) VALUES (:yo, :otro)';
        $consulta = $conexion->prepare($sql);
        $consulta->bindParam(':yo', $mi_id);
        $consulta->bindParam(':otro', $destino_id);
        $consulta->execute();
    }

    return 'Solicitud de amistad enviada.';
}
