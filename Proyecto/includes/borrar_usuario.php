<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\Aplicacion;

//Si no eres admin no hace nada
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

//Filtramos la entrada para que id sólo pueda tomar valores de int
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

//Si tenemos un id válido
if ($id) {
	//Borramos el usuario con ese id
    es\ucm\fdi\aw\Usuario::borraPorId((int)$id);
}

header('Location: ../admin_usuarios.php');
exit();