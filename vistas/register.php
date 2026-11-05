<?php

$errores = $_SESSION['errores'] ?? array();
unset($_SESSION['errores']);
$username = $_SESSION['form_username'] ?? '';
unset($_SESSION['form_username']);
$nombre = $_SESSION['form_nombre'] ?? '';
unset($_SESSION['form_nombre']);
$email = $_SESSION['form_email'] ?? '';
unset($_SESSION['form_email']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - WebsApp</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <main class="formulario">

        <h1>WebsApp</h1>
        <h2>Crear cuenta</h2>

        <?php foreach ($errores as $error) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST" action="register.php">

            <p>
                Username:<br>
                <input type="text" name="username" maxlength="30" value="<?php echo htmlspecialchars($username); ?>">
            </p>

            <p>
                Nombre real:<br>
                <input type="text" name="nombre" maxlength="100" value="<?php echo htmlspecialchars($nombre); ?>">
            </p>

            <p>
                Email:<br>
                <input type="email" name="email" maxlength="150" value="<?php echo htmlspecialchars($email); ?>">
            </p>

            <p>
                Contraseña (mínimo 8 caracteres):<br>
                <input type="password" name="password">
            </p>

            <p>
                Repite la contraseña:<br>
                <input type="password" name="password2">
            </p>

            <button type="submit" class="aceptar">Registrarme</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>

    </main>
</body>
</html>
