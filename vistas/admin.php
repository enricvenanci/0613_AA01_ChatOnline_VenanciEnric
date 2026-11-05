<?php

$mi_username = $_SESSION['username'];
$datos = $_SESSION['admin_busqueda'];
unset($_SESSION['admin_busqueda']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar usuarios - WebsApp</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <main class="chat">
        <aside class="sidebar">
            <header class="cabecera">
                <h1>WebsApp</h1>
                <div class="acciones">
                    <span class="usuario-logueado"><?php echo htmlspecialchars($mi_username); ?></span>
                    <a class="logout" href="chat.php">Volver al chat</a>
                    <a class="logout" href="../acciones/logout.php">Cerrar sesión</a>
                </div>
            </header>

            <?php if ($datos['mensaje'] != '') { ?>
                <p class="mensaje-aviso"><?php echo htmlspecialchars($datos['mensaje']); ?></p>
            <?php } ?>
            <?php foreach ($datos['errores'] as $error) { ?>
                <p class="mensaje-aviso"><?php echo htmlspecialchars($error); ?></p>
            <?php } ?>

            <form method="GET" action="admin.php">
                <input type="search" name="buscar" maxlength="100"
                       placeholder="Buscar por username, nombre o email..."
                       aria-label="Buscar usuarios" value="<?php echo htmlspecialchars($datos['buscar']); ?>">
                <button type="submit" class="aceptar">Buscar</button>
            </form>

            <section class="lista">
                <h2>Gestión de usuarios</h2>
                <?php if (!$datos['buscado']) { ?>
                    <p class="vacio">Busca un usuario para modificar sus datos o borrar su cuenta.</p>
                <?php } elseif (empty($datos['resultados']) && empty($datos['errores'])) { ?>
                    <p class="vacio">No se han encontrado usuarios.</p>
                <?php } ?>

                <?php foreach ($datos['resultados'] as $usuario) { ?>
                    <div class="persona solicitud">
                        <form method="POST" action="../acciones/actualizar_usuario.php">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($datos['token']); ?>">
                            <input type="hidden" name="usuario_id" value="<?php echo (int)$usuario['id']; ?>">
                            <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($datos['buscar']); ?>">
                            <p>Username:<br>
                                <input type="text" name="username" maxlength="30" required
                                       value="<?php echo htmlspecialchars($usuario['username']); ?>">
                            </p>
                            <p>Nombre real:<br>
                                <input type="text" name="nombre" maxlength="100" required
                                       value="<?php echo htmlspecialchars($usuario['nombre_real']); ?>">
                            </p>
                            <p>Email:<br>
                                <input type="email" name="email" maxlength="150" required
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>">
                            </p>
                            <p>Nueva contraseña (opcional):<br>
                                <input type="password" name="password" autocomplete="new-password">
                            </p>
                            <p>Repite la nueva contraseña:<br>
                                <input type="password" name="password2" autocomplete="new-password">
                            </p>
                            <p>Rol: <?php echo htmlspecialchars($usuario['rol']); ?></p>
                            <button type="submit" class="aceptar">Guardar</button>
                        </form>

                        <form method="POST" action="../acciones/eliminar_usuario.php">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($datos['token']); ?>">
                            <input type="hidden" name="usuario_id" value="<?php echo (int)$usuario['id']; ?>">
                            <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($datos['buscar']); ?>">
                            <?php if ((int)$usuario['id'] == (int)$_SESSION['usuario_id']) { ?>
                                <button type="submit" class="aceptar" disabled>No puedes borrar tu cuenta</button>
                            <?php } else { ?>
                                <button type="submit" class="aceptar">Borrar usuario</button>
                            <?php } ?>
                        </form>
                    </div>
                <?php } ?>
            </section>
        </aside>
    </main>
</body>
</html>
