<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\productos\Producto;
use es\ucm\fdi\aw\ofertas\Oferta;

$estilosExtra = ['carrito.css'];

$rutaApp = RUTA_APP;

// ----------------------------------------------------------------------------
// CONTROL DE ACCESO
// ----------------------------------------------------------------------------
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

// Obtenemos los datos de la sesión
$carrito = $_SESSION['carrito'] ?? [];
$ofertas_aplicadas = $_SESSION['ofertas_aplicadas'] ?? [];

// ----------------------------------------------------------------------------
// CASO: CARRITO VACÍO
// ----------------------------------------------------------------------------
if (empty($carrito)) {
    $contenidoPrincipal = <<<EOS
        <h1>Tu Pedido</h1>
        <p>Tu carrito está vacío ahora mismo.</p>
        <p><a href="$rutaApp/carta.php" class="carrito-empty-link">Volver a la Carta</a></p>
    EOS;
} else {
    //Inicializamos algunas variables del desglose
    $subtotalPedido = 0;
    $descuento = 0;

    // ========================================================================
    // SECCIÓN 1: GENERACIÓN DE FILAS (PRODUCTOS Y OFERTAS APLICADAS)
    // ========================================================================
   
    // --- Parte A: Filas de Productos ---
    $productos = Producto::porIds(array_keys($carrito));
    $filasTablaProductos = "";
    foreach ($productos as $fila) {
        $id = $fila->getId();
        // Obtenemmos todos los datos necesarios de la fila
        $nombreProducto = $fila->getNombre();
        $precioUnitario = number_format($fila->getPrecioConIva(), 2, '.', '');
        $cantidad = $carrito[$id]['total'];
        $subtotalProducto = number_format($precioUnitario * $cantidad, 2, '.', '');

        //Sumamos el subtotal del producto al subtotal del pedido
        $subtotalPedido += $subtotalProducto;
        

        $filasTablaProductos .= <<<EOS
            <tr>
                <td class='carrito-tabla td-producto'>{$nombreProducto}</td>
                <td>{$precioUnitario} €</td>
                <td>
                    <div class='carrito-cantidad-inner'>
                        <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                            <input type='hidden' name='id_producto' value='{$id}'>
                            <input type='hidden' name='accion' value='add'>
                            <input type='hidden' name='cantidad' value='-1'>
                            <button type='submit' class='carrito-boton-cantidad'>−</button>
                        </form>
                        <strong>{$cantidad}</strong>
                        <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                            <input type='hidden' name='id_producto' value='{$id}'>
                            <input type='hidden' name='accion' value='add'>
                            <input type='hidden' name='cantidad' value='1'>
                            <button type='submit' class='carrito-boton-cantidad'>+</button>
                        </form>
                    </div>
                </td>
                <td><strong>{$subtotalProducto} €</strong></td>
                <td>
                    <form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_producto' value='{$id}'>
                        <input type='hidden' name='accion' value='remove'>
                        <button type='submit' class='carrito-boton-quitar'>Quitar</button>
                    </form>
                </td>
            </tr>
        EOS;
    }
    $subtotalPedido = number_format($subtotalPedido, 2, '.', '');

    // --- Parte B: Filas de Ofertas Aplicadas (Descuentos) ---
    
    foreach ($ofertas_aplicadas as $idOferta => $veces) {
        $oferta = Oferta::porID($idOferta);

        $nombreOferta = $oferta->getNombre();
        $descuentoUnitario = Oferta::calcularDescuentoMonetario($idOferta);
        $subtotalDescuento = number_format($descuentoUnitario * $veces, 2, '.', '');

        $descuento += $subtotalDescuento;
        
        $filasTablaProductos .= <<<EOS
            <tr class='carrito-fila-descuento'>
                <td class='carrito-tabla td-producto'>{$nombreOferta}</td>
                <td>{$descuentoUnitario}€</td>
                <td>{$veces}</td>
                <td><strong class='carrito-descuento'>-{$subtotalDescuento} €</strong></td>
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
    $descuento = number_format($descuento, 2, '.', '');

    // --- Montaje final de la tabla de productos ---
    $htmlTablaProductos = <<<EOS
		<div class="carrito-wrapper"> <table class='carrito-tabla'>
				<thead>
					<tr>
						<th>Producto</th>
						<th>Precio Ud.</th>
						<th>Cantidad</th>
						<th>Subtotal</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>$filasTablaProductos</tbody>
			</table>
		</div>
	EOS;

    // ========================================================================
    // SECCIÓN 2: DESGLOSE DE TOTALES (SUBTOTAL, AHORRO Y TOTAL)
    // ========================================================================

    $totalPedido = $subtotalPedido - $descuento;

    if ($descuento > 0) {
        $htmlDesglose = <<<EOS
            <h2 class='carrito-total'>Subtotal: {$subtotalPedido} €</h2>
            <h2 class='carrito-total carrito-descuento'>Ahorro por ofertas -{$descuento} €</h2>
            <h2 class='carrito-total'>Total a pagar: {$totalPedido} €</h2>
        EOS;
    } else {
        $htmlDesglose = <<<EOS
            <h2 class='carrito-total'>Total a pagar: {$totalPedido} €</h2>
        EOS;
    }

    // ========================================================================
    // SECCIÓN 3: TABLA DE OFERTAS DISPONIBLES (PARA AÑADIR)
    // ========================================================================
	$ofertas = Oferta::ofertasActivas();
	$filasTablaOfertas = "";
	foreach ($ofertas as $fila) {
		$idOferta = $fila->getId();
		if (Oferta::esAplicable($idOferta, $carrito)) {
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
				
				$filasTablaOfertas .= <<<EOS
					<tr>
						<td class='carrito-tabla td-producto'>{$nombre}</td>
						<td>{$productosIncluidos}</td>
						<td>{$fechaFin->format('d/m/Y')}</td>
						<td>{$descuento}%</td>
						<td><del>{$pvpBaseHTML}€</del> <br/> <strong>{$pvpFinalHTML}€</strong></td>
						<td>
							<form action='$rutaApp/includes/procesar_carrito.php' method='POST'>
								<input type='hidden' name='id_oferta' value='{$fila->getId()}'>
								<input type='hidden' name='accion' value='aplicarOferta'>
								<button type='submit' class='carrito-boton-anadirOferta'>Aplicar</button>
							</form>
						</td>
					</tr>
				EOS;
		}
	}
	
	$htmlOfertas = "";
	if (!empty($filasTablaOfertas)) {
		$htmlOfertas .= <<<EOS
			<div class="carrito-wrapper"> <table class='carrito-tabla'>
					<thead>
						<tr>
							<th>Nombre</th>
							<th class='carrito-col-productos'>Productos incluidos</th>
							<th>Fecha fin</th>
							<th>% de descuento</th>
							<th>Precio final</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>$filasTablaOfertas</tbody>
				</table>
			</div>
EOS;
	} else {
		$htmlOfertas .= "<p>No puedes aplicar ninguna oferta</p>";
	}

    // ========================================================================
    // SECCIÓN 4: FORMULARIO DE ENTREGA Y PAGO
    // ========================================================================
    $htmlFormularioEntrega = <<<EOS
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

    // --- Montaje Final ---
    $contenidoPrincipal = <<<EOS
        <h3><a href='$rutaApp/carta.php'>⬅ Seguir comprando</a></h3>
        <h1>Revisar Pedido</h1>
        $htmlTablaProductos
        $htmlDesglose
        <h1>Ofertas aplicables</h1>
        $htmlOfertas
        $htmlFormularioEntrega
    EOS;
}

// Renderizar plantilla
require __DIR__.'/includes/vistas/plantillas/plantilla.php';