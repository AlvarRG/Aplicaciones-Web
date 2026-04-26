<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\productos\Producto;
use es\ucm\fdi\aw\ofertas\Oferta;

$estilosExtra = ['carrito.css'];

$rutaApp = RUTA_APP;

//Si el usuario no ha iniciado sesión, mostramos un mensaje para que haga login antes de pedir
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $tituloPagina       = 'Inicia Sesión';
    $contenidoPrincipal = <<<EOS
        <div class="carrito-login-wrapper">
            <h2 class="carrito-login-title">Necesitas iniciar sesión</h2>
            <p>Para poder realizar un pedido en Bistro FDI, debes identificarte primero.</p>
            <a href="$rutaApp/login.php" class="carrito-login-link">Ir al Login</a>
        </div>
    EOS;
    require __DIR__.'/includes/vistas/plantillas/plantilla.php';
    exit();
}

$tituloPagina = 'Revisar Pedido';

//Obtenemos el carrito de la sesión (array id_producto => cantidad)
$carrito = $_SESSION['carrito'] ?? [];
$ofertas_aplicadas = $_SESSION['ofertas_aplicadas'] ?? [];

if (empty($carrito)) {
    //Carrito vacío: informamos al usuario y le invitamos a volver a la carta
    $contenidoPrincipal = <<<EOS
        <h1>Tu Pedido</h1>
        <p>Tu carrito está vacío ahora mismo.</p>
        <p><a href="$rutaApp/carta.php" class="carrito-empty-link">Volver a la Carta</a></p>
    EOS;
} else {
    $ids = array_keys($carrito);
    $productos = Producto::porIds($ids);

    //Construimos las filas y el total acumulado
    $filasTabla  = "";
    $totalPedido = 0;

    foreach ($productos as $fila) {
        $id       = $fila->getId();
        $cantidad = $carrito[$id];

        //Precio unitario con IVA y subtotal para esta línea
        $precioUd     = $fila->getPrecioConIva();
        $subtotal     = $precioUd * $cantidad;
        $totalPedido += $subtotal;

        $precioUdFmt = number_format($precioUd, 2, '.', '');
        $subtotalFmt = number_format($subtotal, 2, '.', '');

        // Si hay ofertas aplicadas, el botón "Quitar" y el "−" piden confirmación
        $onclickAttr = !empty($ofertas_aplicadas) ? "onclick=\"return confirm('¿Estás seguro? Se eliminarán todas las ofertas aplicadas.')\"" : "";

        // Calcular cantidades para los botones + y −
        $cantMenos = $cantidad - 1;
        $cantMas = $cantidad + 1;

        $filasTabla .= <<<EOS
            <tr>
                <td class='carrito-tabla td-producto'>{$fila->getNombre()}</td>
                <td>{$precioUdFmt} €</td>
                <td class='carrito-cantidad'>
                    <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_producto' value='{$id}'>
                        <input type='hidden' name='accion' value='update'>
                        <input type='hidden' name='cantidad' value='{$cantMenos}'>
                        <button type='submit' class='carrito-boton-cantidad' {$onclickAttr}>−</button>
                    </form>
                    <strong>{$cantidad}</strong>
                    <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_producto' value='{$id}'>
                        <input type='hidden' name='accion' value='update'>
                        <input type='hidden' name='cantidad' value='{$cantMas}'>
                        <button type='submit' class='carrito-boton-cantidad'>+</button>
                    </form>
                </td>
                <td><strong>{$subtotalFmt} €</strong></td>
                <td>
                    <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_producto' value='{$id}'>
                        <input type='hidden' name='accion' value='remove'>
                        <button type='submit' class='carrito-boton-quitar' {$onclickAttr}>Quitar</button>
                    </form>
                </td>
            </tr>
        EOS;
    }

    // Calcular descuentos de ofertas aplicadas
    $descuentoGlobal = 0;
    foreach ($ofertas_aplicadas as $idOferta => $veces) {
        // Obtener los datos de la oferta
        $oferta = Oferta::porID($idOferta);
        $descuento = $oferta->getDescuento();
        $nombreOferta = $oferta->getNombre();

        // Calcular el precio del pack sin descuento
        $productosDeLaOfertaAplicada = Oferta::obtenerProductosOferta($idOferta);

        $precioPack = 0;
        foreach ($productosDeLaOfertaAplicada as $prod) {
            $precioConIva = $prod['precio_base'] * (1 + $prod['iva'] / 100);
            $precioPack += ($precioConIva * $prod['cantidad']);
        }
        
        // Calcular el descuento unitario (1 pack) y el descuento de esta oferta (unitario × veces)
        $descuentoUnitario = $precioPack * ($descuento / 100);
        $descuentoUnitarioFmt = number_format($descuentoUnitario, 2, '.', '');
        $descuentoOferta = $descuentoUnitario * $veces;
        $descuentoOfertaFmt = number_format($descuentoOferta, 2, '.', '');

        // Acumular en el descuento global (suma de todas las ofertas)
        $descuentoGlobal += $descuentoOferta;
        
        $filasTabla .= <<<EOS
            <tr class='carrito-fila-descuento'>
                <td class='carrito-tabla td-producto'>{$nombreOferta}</td>
                <td>{$descuentoUnitarioFmt}€</td>
                <td>{$veces}</td>
                <td><strong class='carrito-descuento'>-{$descuentoOfertaFmt} €</strong></td>
                <td>
                    <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_oferta' value='{$idOferta}'>
                        <input type='hidden' name='accion' value='quitarOferta'>
                        <button type='submit' class='carrito-boton-quitar'>Quitar</button>
                    </form>
                </td>
            </tr>
        EOS;
    }


    // Mostrar desglose si hay descuento
    $totalFinal = $totalPedido - $descuentoGlobal;
    $totalPedidoFmt = number_format($totalPedido, 2, '.', '');
    $totalFinalFmt = number_format($totalFinal, 2, '.', '');
    if ($descuentoGlobal > 0) {
        $descuentoGlobalFmt = number_format($descuentoGlobal, 2, '.', '');
        $htmlTotal = <<<EOS
            <h2 class='carrito-total'>Subtotal: {$totalPedidoFmt} €</h2>
            <h2 class='carrito-total carrito-descuento'>Ahorro por ofertas: -{$descuentoGlobalFmt} €</h2>
            <h2 class='carrito-total'>Total a pagar: {$totalFinalFmt} €</h2>
        EOS;
    } else {
        $htmlTotal = <<<EOS
            <h2 class='carrito-total'>Total a pagar: {$totalPedidoFmt} €</h2>
        EOS;
    }

    //Tabla completa con las filas construidas
    $htmlArticulos = <<<EOS
        <table class='carrito-tabla'>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Ud.</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>$filasTabla</tbody>
        </table>
        {$htmlTotal}
    EOS;
	
	$ofertas = Oferta::ofertasActivas();
	$filasTablaOfertas = "";
    $productosLibres = Oferta::calcularProductosLibres($carrito, $ofertas_aplicadas);
	
	foreach ($ofertas as $fila) {
        $nombre = $fila->getNombre();
        $productosDeLaOferta = Oferta::obtenerProductosOferta($fila->getId());
        $textosProductos = [];
        $precioOriginalLote = 0;
    
        foreach ($productosDeLaOferta as $prod) {
            $textosProductos[] = $prod['nombre'] . ' (x' . $prod['cantidad'] . ')'; 
            $precioConIva = $prod['precio_base'] * (1 + $prod['iva'] / 100);
            $precioOriginalLote += ($precioConIva * $prod['cantidad']);
        }
        $productosIncluidos = implode(', <br>', $textosProductos);
        
        $fechaFin = new DateTime($fila->getFechaFin());
        
        $descuento = $fila->getDescuento();
        $precioFinalCalculado = $precioOriginalLote * (1 - ($descuento / 100));
        $pvpBaseHTML = number_format($precioOriginalLote, 2, ',', '.');
        $pvpFinalHTML = number_format($precioFinalCalculado, 2, ',', '.');
      
            
        $botonHTML = "<form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_oferta' value='{$fila->getId()}'>
                        <input type='hidden' name='accion' value='aplicar_oferta'>
                        <button type='submit' class='carrito-boton-anadirOferta'>Añadir</button>
                    </form>";
        
        $filasTablaOfertas .= <<<EOS
            <tr>
                <td class='carrito-tabla td-producto'>{$nombre}</td>
                <td>{$productosIncluidos}</td>
                <td>{$fechaFin->format('d/m/Y')}</td>
                <td>{$descuento}%</td>
                <td><del>{$pvpBaseHTML}€</del> <br/> <strong>{$pvpFinalHTML}€</strong></td>
                <td>{$botonHTML}</td>
            </tr>
        EOS;
	}
	
	//Tabla completa de ofertas con las filas construidas
	$htmlOfertas = "";
	if (!empty($filasTablaOfertas)) {
		$htmlOfertas .= <<<EOS
        <table class='carrito-tabla'>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th class='carrito-col-productos'>Productos incluidos</th>
                    <th>Fecha fin</th>
                    <th>% de descuento</th>
					<th>Precio final</th>
					<th>Añadir</th>
                </tr>
            </thead>
            <tbody>$filasTablaOfertas</tbody>
        </table>
EOS;
	}
	else {
		$htmlOfertas .= <<<EOS
		<p>No puedes aplicar ninguna oferta</p>
EOS;
	}

    //Formulario de opciones de entrega y acceso al pago
    $htmlFormulario = <<<EOS
        <div class="carrito-resumen">
            <h3>Opciones de entrega</h3>

            <form action="$rutaApp/pago.php" method="POST">
                <div>
                    <label class="carrito-opciones-label">
                        <input type="radio" name="tipo_pedido" value="Local" required>
                        Consumir en el local (Bistro FDI)
                    </label>
                    <br>
                    <br>
                    <label class="carrito-opciones-label">
                        <input type="radio" name="tipo_pedido" value="Llevar" required>
                        Para llevar
                    </label>
                </div>

                <div class="carrito-acciones">
                    <button type="submit" class="carrito-boton-pago">Ir al Pago</button>
                </div>
            </form>
        </div>

        <div class="carrito-cancelar-wrapper">
            <form action="$rutaApp/includes/procesar_carrito.php" method="POST">
                <input type="hidden" name="accion" value="vaciar">
                <button type="submit" class="carrito-boton-cancelar">Cancelar Pedido (Vaciar carrito)</button>
            </form>
        </div>
    EOS;

    //Montamos el contenido final: tabla de artículos + tabla de ofertas + formulario de entrega
    $contenidoPrincipal = <<<EOS
        <p><a href='$rutaApp/carta.php'>⬅ Seguir comprando</a></p>
        <h1>Revisar Pedido</h1>
        $htmlArticulos
        <h1>Ofertas aplicables</h1>
        $htmlOfertas
        $htmlFormulario
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';