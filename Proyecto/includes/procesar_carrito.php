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
	//Si queremos añadir (o restar si $cantidad es negativa)
    if ($accion === 'add') {
        if (isset($_SESSION['carrito'][$id_producto])) {
            // Aplicamos el cambio
            $_SESSION['carrito'][$id_producto]['total'] += $cantidad;
            $_SESSION['carrito'][$id_producto]['disponible'] += $cantidad;
            // Si le hemos dado al boton de '-'
            if ($cantidad === -1) {
                // Si da -1, es porque habia una oferta aplicada y hay que quitarla
                if ($_SESSION['carrito'][$id_producto]['disponible'] === -1) {
                    Oferta::quitarOferta($id_producto, $_SESSION['carrito'], $_SESSION['ofertas_aplicadas']);
                }
                // Si el total llega a 0, eliminamos el producto
                if ($_SESSION['carrito'][$id_producto]['total'] === 0) {
                    unset($_SESSION['carrito'][$id_producto]);
                }
            }
        } else {
            $_SESSION['carrito'][$id_producto] = ['total' => $cantidad, 'disponible' => $cantidad];
        }
    } 
    elseif ($accion === 'remove') { //Si queremos quitar algo del carrito
        while (isset($_SESSION['carrito'][$id_producto])) {
            // Restamos 1 a las unidades totales y disponibles
            $_SESSION['carrito'][$id_producto]['total'] -= 1;
            $_SESSION['carrito'][$id_producto]['disponible'] -= 1;

            // Si da -1, es porque habia una oferta aplicada y hay que quitarla
            if ($_SESSION['carrito'][$id_producto]['disponible'] === -1) {
                Oferta::quitarOferta($id_producto, $_SESSION['carrito'], $_SESSION['ofertas_aplicadas']);
            }

            // Si llegamos a 0 unidades totales, eliminamos la entrada del producto
            if ($_SESSION['carrito'][$id_producto]['total'] <= 0) {
                unset($_SESSION['carrito'][$id_producto]);
            }
        }
    }
} else if ($id_oferta > 0) { //Acciones sobre una oferta específica (necesitan ID)
    if ($accion === 'aplicarOferta') { 
        $vecesAplicables = Oferta::calcularVecesAplicable($id_oferta, $_SESSION['carrito']);

        if (isset($_SESSION['ofertas_aplicadas'][$id_oferta])) {
            $_SESSION['ofertas_aplicadas'][$id_oferta] += $vecesAplicables;
        } else {
            $_SESSION['ofertas_aplicadas'][$id_oferta] = $vecesAplicables;
        }

        // Consumimos los productos del campo disponible
        $productosOferta = Oferta::obtenerProductosOferta($id_oferta);
        foreach ($productosOferta as $producto) {
            $cantidadConsumir = $producto['cantidad'] * $vecesAplicables;
            $_SESSION['carrito'][$producto['id']]['disponible'] -= $cantidadConsumir;
        }
    } else if ($accion === 'quitarOferta') {
         $vecesAplicadas = $_SESSION['ofertas_aplicadas'][$id_oferta];
        
        // Devolvemos los productos al campo disponible
        $productosOferta = Oferta::obtenerProductosOferta($id_oferta);
        foreach ($productosOferta as $producto) {
            $cantidadDevolver = $producto['cantidad'] * $vecesAplicadas;
            $_SESSION['carrito'][$producto['id']]['disponible'] += $cantidadDevolver;
        }
		
		// Quitamos la oferta
		unset($_SESSION['ofertas_aplicadas'][$id_oferta]);
	}
}

// Devolver JSON si es una petición AJAX
if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    $totalArticulos = 0;
    if (isset($_SESSION['carrito'])) {
        $totalArticulos = array_sum(array_column($_SESSION['carrito'], 'total'));
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'totalArticulos' => $totalArticulos
    ]);
    exit();
}

//Redirigir al usuario de vuelta
$urlDestino = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../carta.php';
header("Location: $urlDestino");
exit();