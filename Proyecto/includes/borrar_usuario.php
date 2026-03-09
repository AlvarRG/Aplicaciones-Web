<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    die("No tienes permiso.");
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    $conn = Aplicacion::getInstance()->getConexionBd();
    $conn->query("DELETE FROM Usuarios WHERE id = $id");
}

header('Location: ../admin_usuarios.php');
exit();