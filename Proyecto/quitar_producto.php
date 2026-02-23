<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    $conn = conexionBD();
    
    // Opcional: Podrías querer borrar también el archivo de imagen del servidor
    $res = $conn->query("SELECT imagen FROM Productos WHERE id = $id");
    $p = $res->fetch_assoc();
    if ($p['imagen'] && $p['imagen'] != 'prod_default.png') {
        @unlink("img/productos/" . $p['imagen']); // Borra el archivo físico
    }

    $conn->query("DELETE FROM Productos WHERE id = $id");
}

header('Location: admin_productos.php');
exit();