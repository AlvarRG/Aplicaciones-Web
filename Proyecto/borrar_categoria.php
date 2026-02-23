<?php
require_once __DIR__.'/utils.php';
session_start();
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if ($id) {
    $conn = conexionBD();
    // Nota: Si hay productos en esta categoría, fallará por la clave foránea (lo cual es bueno)
    $conn->query("DELETE FROM Categorias WHERE id = $id");
}
header('Location: admin_categorias.php');