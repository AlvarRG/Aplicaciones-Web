<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\Producto;

//Si no eres admin no hace nada
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: ../index.php'); exit();
}

//Si el id existe
if (isset($_GET['id'])) {
	//Cogemos el valor tipo in del id
    $id = intval($_GET['id']);
    
    //En lugar de DELETE (lo borraría de la base de datos), usamos UPDATE para ocultarlo
    Producto::retirarDeCarta($id);
    header('Location: ../admin_productos.php');
}