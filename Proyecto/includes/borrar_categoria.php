<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\Aplicacion;

//Si no eres admin no hace nada
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

//Filtramos la entrada para que id sólo pueda tomar valores de int
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

//Si tenemos un id válido
if ($id) {
	//Borramos la categoría con ese id, pero si hay productos en esta categoría, fallará por la clave foránea (hay que borrar todos los productos pertenecientes a una categoría antes que esta)
    $queryBorrarCategoria = "DELETE FROM Categorias WHERE id = ?";
    Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarCategoria, "i", (int)$id);
}
header('Location: ../admin_categorias.php');