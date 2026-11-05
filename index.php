<?php

session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: pages/chat.php');
} else {
    header('Location: pages/login.php');
}

exit();
