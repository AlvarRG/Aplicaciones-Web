<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    die("No tienes permiso.");
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    $conn = Aplicacion::getInstance()->getConexionBd();
    // 1. Borrar roles primero (por integridad referencial)
    $conn->query("DELETE FROM RolesUsuario WHERE usuario = $id");
    // 2. Borrar usuario
    $conn->query("DELETE FROM Usuarios WHERE id = $id");
}

header('Location: admin_usuarios.php');
exit();