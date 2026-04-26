<?php
require_once __DIR__.'/config.php';

use es\ucm\fdi\aw\ofertas\Oferta;

//Si alguien intenta entrar sin enviar el formulario, lo echamos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../carta.php');
    exit();
}

//Inicializar el carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

//Inicializar las ofertas aplicadas
if (!isset($_SESSION['ofertas_aplicadas'])) {
    $_SESSION['ofertas_aplicadas'] = [];
}

//Recoger los datos enviados por el formulario
$id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$id_oferta = isset($_POST['id_oferta']) ? (int)$_POST['id_oferta'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
$accion = isset($_POST['accion']) ? $_POST['accion'] : 'add';

//Si quieremos vaciar
if ($accion === 'vaciar') {
    //Vaciamos el array del carrito y las ofertas aplicadas
    $_SESSION['carrito'] = []; 
    $_SESSION['ofertas_aplicadas'] = [];
}

//Acciones sobre un producto específico (necesitan ID)
if ($id_producto > 0) {
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
            // Si la cantidad baja, vaciamos las ofertas (el usuario ya confirmó)
            if ($cantidad < $_SESSION['carrito'][$id_producto]) {
                $_SESSION['ofertas_aplicadas'] = [];
            }
            $_SESSION['carrito'][$id_producto] = $cantidad;
        } else {
            unset($_SESSION['carrito'][$id_producto]);
            $_SESSION['ofertas_aplicadas'] = [];
        }
    } 
    elseif ($accion === 'remove') { //Si queremos quitar algo del carrito
		//Quitamos el producto
        unset($_SESSION['carrito'][$id_producto]);
        //Vaciamos las ofertas aplicadas (el usuario ya confirmó)
        $_SESSION['ofertas_aplicadas'] = [];
    }
}elseif ($id_oferta > 0) { //Acciones sobre una oferta específica (necesitan ID)
    if ($accion === 'aplicar_oferta') { 
		//Calculamos el número de productos libres
        $productos_libres = Oferta::calcularProductosLibres($_SESSION['carrito'], $_SESSION['ofertas_aplicadas']);

        //Sobre los productos libres calculamos si es aplicable
        if (Oferta::esAplicable($id_oferta, $productos_libres)) {
            if (isset($_SESSION['ofertas_aplicadas'][$id_oferta])) {
                $_SESSION['ofertas_aplicadas'][$id_oferta] += 1;
            } else {
                $_SESSION['ofertas_aplicadas'][$id_oferta] = 1;
            }
        } else {
            $_SESSION['error_oferta'] = $id_oferta;
        }

    }else if ($accion === 'quitarOferta') {
		// Quitamos una unidad de la oferta
		$_SESSION['ofertas_aplicadas'][$id_oferta] -= 1;
		
		// Si quedan unidades, se mantiene, si no, se elimina la clave del array
		if ($_SESSION['ofertas_aplicadas'][$id_oferta] === 0) {
			unset($_SESSION['ofertas_aplicadas'][$id_oferta]);
		}
	}
}

//Redirigir al usuario de vuelta
$urlDestino = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../carta.php';
header("Location: $urlDestino");
exit();