<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: index.php'); exit();
}

if (isset($_GET['id'])) {
    $conn = conexionBD();
    $id = intval($_GET['id']);
    
    // En lugar de DELETE, usamos UPDATE para ocultarlo
    $query = "UPDATE Productos SET ofertado = 0, disponible = 0 WHERE id = $id";
    
    if ($conn->query($query)) {
        header('Location: admin_productos.php');
    } else {
        echo "Error al retirar el producto: " . $conn->error;
    }
}