<?php

// Aviso dejado por otra página
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

$errores = $_SESSION['errores'] ?? array();
unset($_SESSION['errores']);

$username = $_SESSION['form_username'] ?? '';
unset($_SESSION['form_username']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - WebsApp</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <main class="formulario">

        <h1>WebsApp</h1>
        <h2>Iniciar sesión</h2>

        <?php if ($mensaje != '') { ?>
            <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php } ?>

        <?php foreach ($errores as $error) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST" action="login.php">

            <p>
                Username:<br>
                <input type="text" name="username" maxlength="30" value="<?php echo htmlspecialchars($username); ?>">
            </p>

            <p>
                Contraseña:<br>
                <input type="password" name="password">
            </p>

            <button type="submit" class="aceptar">Entrar</button>
        </form>

        <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>

    </main>
</body>
</html>
