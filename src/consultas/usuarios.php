<?php

// Devuelve los datos del usuario, o false si no existe.
function buscar_usuario($conexion, $username)
{
    $sql = 'SELECT id, username, password_hash, rol FROM usuarios WHERE username = :username';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':username', $username);
    $consulta->execute();

    return $consulta->fetch(PDO::FETCH_ASSOC);
}

function username_existe($conexion, $username)
{
    $sql = 'SELECT id FROM usuarios WHERE username = :username';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':username', $username);
    $consulta->execute();

    if ($consulta->fetch()) {
        return true;
    }
    return false;
}

function email_existe($conexion, $email)
{
    $sql = 'SELECT id FROM usuarios WHERE email = :email';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':email', $email);
    $consulta->execute();

    if ($consulta->fetch()) {
        return true;
    }
    return false;
}

function crear_usuario($conexion, $username, $nombre, $email, $password)
{
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = 'INSERT INTO usuarios (username, nombre_real, email, password_hash)
            VALUES (:username, :nombre, :email, :hash)';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':username', $username);
    $consulta->bindParam(':nombre', $nombre);
    $consulta->bindParam(':email', $email);
    $consulta->bindParam(':hash', $hash);
    $consulta->execute();
}

function buscar_usuarios_admin($conexion, $texto)
{
    $texto_escapado = addcslashes($texto, '%_' . chr(92));
    $patron = '%' . $texto_escapado . '%';

    $sql = 'SELECT id, username, nombre_real, email, rol
            FROM usuarios
            WHERE username LIKE :patron1 OR nombre_real LIKE :patron2 OR email LIKE :patron3
            ORDER BY username ASC
            LIMIT 20';
    $consulta = $conexion->prepare($sql);
    $consulta->execute(array(
        ':patron1' => $patron,
        ':patron2' => $patron,
        ':patron3' => $patron
    ));

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

function username_existe_en_otro_usuario($conexion, $username, $usuario_id)
{
    $consulta = $conexion->prepare('SELECT id FROM usuarios WHERE username = :username AND id <> :id');
    $consulta->execute(array(':username' => $username, ':id' => $usuario_id));

    return (bool)$consulta->fetchColumn();
}

function email_existe_en_otro_usuario($conexion, $email, $usuario_id)
{
    $consulta = $conexion->prepare('SELECT id FROM usuarios WHERE email = :email AND id <> :id');
    $consulta->execute(array(':email' => $email, ':id' => $usuario_id));

    return (bool)$consulta->fetchColumn();
}

function actualizar_usuario_admin($conexion, $usuario_id, $username, $nombre, $email, $password)
{
    if ($password == '') {
        $sql = 'UPDATE usuarios
                SET username = :username, nombre_real = :nombre, email = :email
                WHERE id = :id';
        $parametros = array(
            ':username' => $username,
            ':nombre' => $nombre,
            ':email' => $email,
            ':id' => $usuario_id
        );
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = 'UPDATE usuarios
                SET username = :username, nombre_real = :nombre, email = :email, password_hash = :hash
                WHERE id = :id';
        $parametros = array(
            ':username' => $username,
            ':nombre' => $nombre,
            ':email' => $email,
            ':hash' => $hash,
            ':id' => $usuario_id
        );
    }

    $consulta = $conexion->prepare($sql);
    $consulta->execute($parametros);

    return $consulta->rowCount() > 0;
}

function eliminar_usuario_admin($conexion, $usuario_id, $mi_id)
{
    if ($usuario_id <= 0 || $usuario_id == $mi_id) {
        return 'No puedes borrar tu propia cuenta desde esta pantalla.';
    }

    $consulta = $conexion->prepare('SELECT rol FROM usuarios WHERE id = :id');
    $consulta->execute(array(':id' => $usuario_id));
    $rol = $consulta->fetchColumn();

    if ($rol === false) {
        return 'Ese usuario ya no existe.';
    }

    if ($rol == 'admin') {
        $consulta = $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
        if ((int)$consulta->fetchColumn() <= 1) {
            return 'No se puede borrar el último administrador.';
        }
    }

    $consulta = $conexion->prepare('DELETE FROM usuarios WHERE id = :id');
    $consulta->execute(array(':id' => $usuario_id));

    return $consulta->rowCount() > 0 ? 'Usuario borrado.' : 'No se pudo borrar el usuario.';
}

function actualizar_ultima_conexion($conexion, $usuario_id)
{
    $sql = 'UPDATE usuarios SET ultima_conexion = CURRENT_TIMESTAMP WHERE id = :id';
    $consulta = $conexion->prepare($sql);
    $consulta->bindParam(':id', $usuario_id);
    $consulta->execute();
}
