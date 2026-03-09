<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$estilosExtra = ['carrito.css'];

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $tituloPagina = 'Inicia Sesión';
    $contenidoPrincipal = <<<EOS
        <div class="carrito-login-wrapper">
            <h2 class="carrito-login-title">Necesitas iniciar sesión</h2>
            <p>Para poder realizar un pedido en Bistro FDI, debes identificarte primero.</p>
            <a href="login.php" class="carrito-login-link">Ir al Login</a>
        </div>
EOS;
    require 'includes/vistas/plantillas/plantilla.php';
    exit();
}

$tituloPagina = 'Revisar Pedido';
$conn = Aplicacion::getInstance()->getConexionBd();

$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    $contenidoPrincipal = <<<EOS
        <h1>Tu Pedido</h1>
        <p>Tu carrito está vacío ahora mismo.</p>
        <p><a href="carta.php" class="carrito-empty-link">Volver a la Carta</a></p>
EOS;
} else {
    $ids = implode(',', array_map('intval', array_keys($carrito)));
    
    $query = "SELECT * FROM Productos WHERE id IN ($ids)";
    $rs = $conn->query($query);
    
    $htmlArticulos = "<table class='carrito-tabla'>";
    $htmlArticulos .= "<thead><tr><th>Producto</th><th>Precio Ud.</th><th>Cantidad</th><th>Subtotal</th><th>Acciones</th></tr></thead><tbody>";
    
    $totalPedido = 0;
    
    while ($fila = $rs->fetch_assoc()) {
        $id = $fila['id'];
        $cantidad = $carrito[$id];
        
        $precioUd = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
        $subtotal = $precioUd * $cantidad;
        $totalPedido += $subtotal;
        
        $precioUdFmt = number_format($precioUd, 2, '.', '');
        $subtotalFmt = number_format($subtotal, 2, '.', '');
        
        $htmlArticulos .= "<tr>";
        $htmlArticulos .= "<td class='carrito-tabla td-producto'>{$fila['nombre']}</td>";
        $htmlArticulos .= "<td>{$precioUdFmt} €</td>";
        $htmlArticulos .= "<td><strong>{$cantidad}</strong></td>";
        $htmlArticulos .= "<td><strong>{$subtotalFmt} €</strong></td>";
        $htmlArticulos .= "<td>
            <form action='includes/procesar_carrito.php' method='POST'>
                <input type='hidden' name='id_producto' value='{$id}'>
                <input type='hidden' name='accion' value='remove'>
                <button type='submit' class='carrito-boton-quitar'>Quitar</button>
            </form>
        </td>";
        $htmlArticulos .= "</tr>";
    }
    
    $totalPedidoFmt = number_format($totalPedido, 2, '.', '');
    $htmlArticulos .= "</tbody></table>";
    $htmlArticulos .= "<h2 class='carrito-total'>Total a pagar: {$totalPedidoFmt} €</h2>";
    
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
                <button type="submit" class="carrito-boton-pago">
                    Ir al Pago
                </button>
            </div>
        </form>
    </div>
    
    <div class="carrito-cancelar-wrapper">
        <form action="includes/procesar_carrito.php" method="POST">
            <input type="hidden" name="accion" value="vaciar">
            <button type="submit" class="carrito-boton-cancelar">
                Cancelar Pedido (Vaciar carrito)
            </button>
        </form>
    </div>
EOS;

    $contenidoPrincipal = "<h1>Revisar Pedido</h1>" . $htmlArticulos . $htmlFormulario;
}

require 'includes/vistas/plantillas/plantilla.php';
?>