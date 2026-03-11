<?php
require_once __DIR__.'/config.php';

//Si alguien intenta entrar sin enviar el formulario, lo echamos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../carta.php');
    exit();
}

//Inicializar el carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

//Recoger los datos enviados por el formulario
$id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
$accion = isset($_POST['accion']) ? $_POST['accion'] : 'add';

//Si quieremos vaciar
if ($accion === 'vaciar') {
    //Vaciamos el array del carrito
    $_SESSION['carrito'] = []; 
}

//Acciones sobre un producto específico (necesitan ID)
elseif ($id_producto > 0) {
	//Si queremos añadir
    if ($accion === 'add') {
		//Si ya existe ese producto en el carrito, le sumamos la cantidad, si no lo inicializamos con esa
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        }
    }
    elseif ($accion === 'update') { //Si queremos actualizar
		//Si la cantidad es mayor que cero se la asignamos al producto, si no lo quitamos del carrito
        if ($cantidad > 0) {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        } else {
            unset($_SESSION['carrito'][$id_producto]);
        }
    } 
    elseif ($accion === 'remove') { //Si queremos quitar algo del carrito
		//Quitamos el producto
        unset($_SESSION['carrito'][$id_producto]);
    }
}

//Redirigir al usuario de vuelta
$urlDestino = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../carta.php';
header("Location: $urlDestino");
exit();