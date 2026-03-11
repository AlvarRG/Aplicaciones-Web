<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\Producto;

//Si no eres admin no hace nada
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

//Filtramos la entrada para que id sólo pueda tomar valores de int
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

//Si tenemos un id válido
if ($id) {
	//Borramos el producto con ese id
    Producto::borrar((int)$id);
}
header('Location: ../admin_productos.php');