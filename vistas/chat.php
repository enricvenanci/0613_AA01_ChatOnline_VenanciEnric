<?php

// Recupera el nombre del usuario que ha iniciado sesión.
$mi_username = $_SESSION['username'];

// Recupera el aviso enviado desde otra página y lo elimina para que solo aparezca una vez.
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

// Recupera los datos preparados por el controlador para mostrar la sección actual.
$seccion = $_SESSION['seccion'];
$buscar = $_SESSION['buscar'];
$buscado = $_SESSION['buscado'];
$errores = $_SESSION['errores'];
$amigos = $_SESSION['amigos'];
$solicitudes = $_SESSION['solicitudes'];
$resultados = $_SESSION['resultados'];

// Limpia los datos temporales de sesión después de copiarlos a variables locales.
unset($_SESSION['seccion'], $_SESSION['buscar'], $_SESSION['buscado'], $_SESSION['errores']);
unset($_SESSION['amigos'], $_SESSION['solicitudes'], $_SESSION['resultados']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Define la codificación, adaptación a móviles, título, icono y estilos de la página. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebsApp</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <!-- Contenedor principal: barra lateral de opciones y panel de bienvenida. -->
    <main class="chat">
        <aside class="sidebar">
            <!-- Muestra la marca, el usuario conectado y el enlace para cerrar sesión. -->
            <header class="cabecera">
                <h1>WebsApp</h1>
                <div class="acciones">
                    <span class="usuario-logueado"><?php echo htmlspecialchars($mi_username); ?></span>
                    <?php if (($_SESSION['rol'] ?? '') == 'admin') { ?>
                        <a class="logout" href="admin.php">Administrar usuarios</a>
                    <?php } ?>
                    <a class="logout" href="../acciones/logout.php">Cerrar sesión</a>
                </div>
            </header>
            <!-- Muestra el aviso general recibido desde otra página, si existe. -->
            <?php if ($mensaje != '') { ?>
                <p class="mensaje-aviso"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php } ?>

            <!-- Muestra los errores que haya generado la última acción del usuario. -->
            <?php foreach ($errores as $error) { ?>
                <p class="mensaje-aviso"><?php echo htmlspecialchars($error); ?></p>
            <?php } ?>

            <!-- Formulario GET para buscar usuarios por nombre o username. -->
            <form method="GET" action="chat.php">
                <input type="hidden" name="seccion" value="buscar">
                <input id="buscador" type="search" name="buscar" maxlength="100"
                       placeholder="Buscar usuarios por username o nombre..."
                       aria-label="Buscar usuarios" value="<?php echo htmlspecialchars($buscar); ?>">
            </form>

            <!-- Permite cambiar entre la lista de amigos y las solicitudes recibidas. -->
            <nav>
                <a <?php if ($seccion == 'amigos') { echo 'class="activo"'; } ?> href="chat.php?seccion=amigos">Amigos</a>
                <a <?php if ($seccion == 'solicitudes') { echo 'class="activo"'; } ?> href="chat.php?seccion=solicitudes">
                    Solicitudes <span id="contador"><?php if (count($solicitudes) > 0) { echo '(' . count($solicitudes) . ')'; } ?></span>
                </a>
            </nav>

            <?php if ($seccion == 'amigos') { ?>

                <!-- Sección de amigos: muestra un aviso si está vacía y cada amigo disponible. -->
                <section class="lista">
                    <h2>Mis amigos</h2>

                    <?php if (empty($amigos)) { ?>
                        <p class="vacio">Aún no tienes amigos. Busca usuarios y envíales una solicitud.</p>
                    <?php } ?>

                    <?php foreach ($amigos as $amigo) { ?>
                        <p class="persona">
                            <!-- Abre la conversación con el amigo seleccionado. -->
                            <a href="conversacion.php?amigo=<?php echo (int)$amigo['id']; ?>#final">
                                <?php echo htmlspecialchars($amigo['username']); ?>
                            </a>
                            <?php if ($amigo['sin_leer'] > 0) { ?>
                                <strong>(<?php echo (int)$amigo['sin_leer']; ?> sin leer)</strong>
                            <?php } ?>
                            <small><?php echo htmlspecialchars($amigo['nombre_real']); ?></small>
                        </p>
                    <?php } ?>
                </section>

            <?php } elseif ($seccion == 'solicitudes') { ?>

                <!-- Sección de solicitudes pendientes con opciones para aceptarlas o rechazarlas. -->
                <section class="lista">
                    <h2>Solicitudes de amistad</h2>

                    <?php if (empty($solicitudes)) { ?>
                        <p class="vacio">No tienes solicitudes pendientes.</p>
                    <?php } ?>

                    <?php foreach ($solicitudes as $solicitud) { ?>
                        <div class="persona solicitud">
                            <span>
                                <?php echo htmlspecialchars($solicitud['username']); ?>
                                <small><?php echo htmlspecialchars($solicitud['nombre_real']); ?></small>
                            </span>

                            <form method="POST" action="../acciones/responder_solicitud.php">
                                <input type="hidden" name="solicitud_id" value="<?php echo (int)$solicitud['id']; ?>">
                                <button type="submit" name="accion" value="aceptar" class="aceptar">Aceptar</button>
                                <button type="submit" name="accion" value="rechazar" class="aceptar">Rechazar</button>
                            </form>
                        </div>
                    <?php } ?>
                </section>

            <?php } else { ?>

                <!-- Sección de resultados de búsqueda, incluidos sus estados y acciones posibles. -->
                <section class="lista">
                    <h2>Resultados de la búsqueda</h2>

                    <?php if (!$buscado) { ?>
                        <p class="vacio">Escribe arriba el username o el nombre de quien quieres buscar.</p>
                    <?php } elseif (empty($resultados) && empty($errores)) { ?>
                        <p class="vacio">No se ha encontrado ningún usuario.</p>
                    <?php } ?>

                    <?php foreach ($resultados as $usuario) { ?>
                        <div class="persona solicitud">
                            <span>
                                <?php echo htmlspecialchars($usuario['username']); ?>
                                <small><?php echo htmlspecialchars($usuario['nombre_real']); ?></small>
                            </span>

                            <?php if ($usuario['estado'] == 'aceptada') { ?>
                                <!-- Si ya son amigos, permite abrir la conversación. -->
                                <a class="logout" href="conversacion.php?amigo=<?php echo (int)$usuario['id']; ?>#final">Abrir chat</a>
                            <?php } elseif ($usuario['estado'] == 'pendiente') { ?>
                                <!-- Indica que la solicitud de amistad todavía no se ha resuelto. -->
                                <small>Solicitud pendiente</small>
                            <?php } else { ?>
                                <!-- Envía una solicitud de amistad y conserva el término de búsqueda. -->
                                <form method="POST" action="../acciones/enviar_solicitud.php">
                                    <input type="hidden" name="usuario_id" value="<?php echo (int)$usuario['id']; ?>">
                                    <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($buscar); ?>">
                                    <button type="submit" class="aceptar">Enviar solicitud</button>
                                </form>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>

            <?php } ?>
        </aside>

        <!-- Panel principal que aparece mientras no se selecciona una conversación. -->
        <section class="bienvenida">
            <div>
                <h2>¡Bienvenido a WebsApp!</h2>
                <p>Busca usuarios, acepta solicitudes o abre un chat con tus amigos.</p>
            </div>
        </section>
    </main>
</body>
</html>
