<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$estilosExtra = ['carrito.css'];

// Si el usuario no ha iniciado sesión, mostramos un mensaje para que haga login antes de pedir
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $tituloPagina       = 'Inicia Sesión';
    $contenidoPrincipal = <<<EOS
        <div class="carrito-login-wrapper">
            <h2 class="carrito-login-title">Necesitas iniciar sesión</h2>
            <p>Para poder realizar un pedido en Bistro FDI, debes identificarte primero.</p>
            <a href="login.php" class="carrito-login-link">Ir al Login</a>
        </div>
    EOS;
    require __DIR__.'/includes/vistas/plantillas/plantilla.php';
    exit();
}

$tituloPagina = 'Revisar Pedido';

// Obtenemos el carrito de la sesión (array id_producto => cantidad)
$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    // Carrito vacío: informamos al usuario y le invitamos a volver a la carta
    $contenidoPrincipal = <<<EOS
        <h1>Tu Pedido</h1>
        <p>Tu carrito está vacío ahora mismo.</p>
        <p><a href="carta.php" class="carrito-empty-link">Volver a la Carta</a></p>
    EOS;
} else {
    // Prepared statement con un placeholder ? por cada producto del carrito
    $ids          = array_keys($carrito);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $tipos        = str_repeat('i', count($ids)); // 'i' = integer por cada id

    $queryProductosCarrito = "SELECT * FROM Productos WHERE id IN ($placeholders)";
    $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosCarrito, $tipos, ...$ids)->get_result();

    // Construimos las filas y el total acumulado
    $filasTabla  = "";
    $totalPedido = 0;

    foreach ($rs as $fila) {
        $id       = $fila['id'];
        $cantidad = $carrito[$id];

        // Precio unitario con IVA y subtotal para esta línea
        $precioUd     = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
        $subtotal     = $precioUd * $cantidad;
        $totalPedido += $subtotal;

        $precioUdFmt = number_format($precioUd, 2, '.', '');
        $subtotalFmt = number_format($subtotal, 2, '.', '');

        $filasTabla .= <<<EOS
            <tr>
                <td class='carrito-tabla td-producto'>{$fila['nombre']}</td>
                <td>{$precioUdFmt} €</td>
                <td><strong>{$cantidad}</strong></td>
                <td><strong>{$subtotalFmt} €</strong></td>
                <td>
                    <form action='includes/procesar_carrito.php' method='POST'>
                        <input type='hidden' name='id_producto' value='{$id}'>
                        <input type='hidden' name='accion' value='remove'>
                        <button type='submit' class='carrito-boton-quitar'>Quitar</button>
                    </form>
                </td>
            </tr>
        EOS;
    }

    $totalPedidoFmt = number_format($totalPedido, 2, '.', '');

    // Tabla completa con las filas construidas
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
        <h2 class='carrito-total'>Total a pagar: {$totalPedidoFmt} €</h2>
    EOS;

    // Formulario de opciones de entrega y acceso al pago
    $htmlFormulario = <<<EOS
        <div class="carrito-resumen">
            <h3>Opciones de entrega</h3>

            <form action="pago.php" method="POST">
                <div>
                    <label class="carrito-opciones-label">
                        <input type="radio" name="tipo_pedido" value="Local" required>
                        Consumir en el local (Bistro FDI)
                    </label>
                    <br><br>
                    <label class="carrito-opciones-label">
                        <input type="radio" name="tipo_pedido" value="Llevar" required>
                        Para llevar
                    </label>
                </div>

                <div class="carrito-acciones">
                    <a href="carta.php">Seguir comprando</a>
                    <button type="submit" class="carrito-boton-pago">Ir al Pago</button>
                </div>
            </form>
        </div>

        <div class="carrito-cancelar-wrapper">
            <form action="includes/procesar_carrito.php" method="POST">
                <input type="hidden" name="accion" value="vaciar">
                <button type="submit" class="carrito-boton-cancelar">Cancelar Pedido (Vaciar carrito)</button>
            </form>
        </div>
    EOS;

    // Montamos el contenido final: tabla de artículos + formulario de entrega
    $contenidoPrincipal = "<h1>Revisar Pedido</h1>" . $htmlArticulos . $htmlFormulario;

    if ($rs) {
        $rs->free();
    }
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';