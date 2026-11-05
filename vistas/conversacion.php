<?php

$mi_id = $_SESSION['usuario_id'];
$mi_username = $_SESSION['username'];

$amigo = $_SESSION['amigo'];
$mensajes = $_SESSION['mensajes'];
$mensaje = $_SESSION['mensaje'] ?? '';  /*pcogemos las variables creadas anteriormente para asi poder leer posteriormente para poder saber que datos es propio o es del amigo */
unset($_SESSION['amigo'], $_SESSION['mensajes'], $_SESSION['mensaje']);/*Quita el seteo de estas variables de session para asi poder trabajar con ellas como si no exisitiarn (le quita toda informacion que haya dentro) */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con <?php echo htmlspecialchars($amigo['username']); ?> - WebsApp</title>  <!--pilla el nombre del usuario que tengas agregado y pone en el titulo de cada persona -->
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <main class="chat chat-abierto">

        <aside class="sidebar">
            <header class="cabecera">
                <h1>WebsApp</h1>
                <div class="acciones">
                    <span class="usuario-logueado"><?php echo htmlspecialchars($mi_username); ?></span> <!-- pone tu user en la cabecera del chat -->
                    <a class="logout" href="../acciones/logout.php">Cerrar sesión</a>
                </div>
            </header>
            <p><a href="chat.php?seccion=amigos">&larr; Volver a mis amigos</a></p>
        </aside>

        <section class="conversacion">

            <header class="cabecera-chat">
                <a class="volver" href="chat.php?seccion=amigos">&larr;</a>
                <h2>
                    <?php echo htmlspecialchars($amigo['username']); ?> <!--Se guarda el nombre de tu amigo y lo pone en la cabecera del chat par saber con quien estas hablando 
                    htmlspecialchars convierte el dtring php en un string que html pueda leer-->
                    <small><?php echo htmlspecialchars($amigo['nombre_real']); ?></small>
                </h2>
                <a class="logout" href="conversacion.php?amigo=<?php echo (int)$amigo['id']; ?>#final">Actualizar</a>
            </header>

            <div class="mensajes">

                <?php if (empty($mensajes)) { ?>
                    <p class="vacio">Aún no hay mensajes. ¡Escribe el primero!</p>
                <?php } ?>

                <?php foreach ($mensajes as $m) { ?>
                    <?php
                    if ($m['emisor_id'] == $mi_id) {
                        $clase = 'mio';
                    } else {
                        $clase = 'otro';
                    }
                    ?>
                    <div class="mensaje <?php echo $clase; ?>">

                        <span class="emisor"><?php echo htmlspecialchars($m['username']); ?></span>
                        <span class="hora"><?php echo date('d/m/Y H:i', strtotime($m['fecha_envio'])); ?></span> <!-- strtotime pasa de string a dato hora-->

                        <?php if ($m['tipo'] == 'imagen' && $m['adjunto_id'] != '') { ?> <!--Coge el archivo seleccionado para mostrarlo por pantalla enel recuadro de mensaje cogiendo su id y mostrandolo-->
                            <img class="adjunto-imagen"
                                 src="../acciones/ver_archivo.php?id=<?php echo (int)$m['adjunto_id']; ?>"
                                 alt="Imagen enviada">
                        <?php } elseif ($m['tipo'] == 'archivo' && $m['adjunto_id'] != '') { ?>
                            <a class="adjunto-archivo"
                               href="../acciones/ver_archivo.php?id=<?php echo (int)$m['adjunto_id']; ?>">
                                Archivo: <?php echo htmlspecialchars($m['nombre_original']); ?>
                            </a>
                        <?php } ?>

                        <?php if ($m['contenido'] != '') { ?>
                            <p class="texto"><?php echo htmlspecialchars($m['contenido']); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div id="final"></div>
            </div>

            <?php if ($mensaje != '') { ?>
                <p class="mensaje-aviso"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php } ?>

            <form class="enviar" method="POST" action="../acciones/enviar_mensaje.php" enctype="multipart/form-data">

                <input type="hidden" name="amigo_id" value="<?php echo (int)$amigo['id']; ?>">

                 <input type="text" name="contenido" maxlength="250" autocomplete="off"   
                       placeholder="Escribe un mensaje (máximo 250 caracteres)">  <!-- seccion para escribir el mensaje-->

                <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.gif,.pdf,.txt">

                <button type="submit" class="aceptar">Enviar</button>
            </form>
        </section>
    </main>

</body>
</html>
