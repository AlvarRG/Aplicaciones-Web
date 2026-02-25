<?php
require_once __DIR__.'/utils.php';
session_start();

// 1. REQUISITO: El usuario debe estar identificado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $tituloPagina = 'Inicia Sesión';
    $contenidoPrincipal = <<<EOS
        <div style="text-align: center; padding: 50px;">
            <h2>⚠️ Necesitas iniciar sesión</h2>
            <p>Para poder realizar un pedido en Bistro FDI, debes identificarte primero.</p>
            <a href="login.php" style="background: #303030; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ir al Login</a>
        </div>
EOS;
    require 'includes/vistas/plantillas/plantilla.php';
    exit();
}

$tituloPagina = 'Revisar Pedido';
$conn = conexionBD();

// 2. Comprobar si el carrito tiene algo
$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    $contenidoPrincipal = <<<EOS
        <h1>Tu Pedido</h1>
        <p>Tu carrito está vacío ahora mismo.</p>
        <p><a href="carta.php" style="color: red; font-weight: bold;">🍽️ Volver a la Carta</a></p>
EOS;
} else {
    // 3. Obtener los datos reales de los productos desde la base de datos
    // Transformamos las claves del array (los IDs) en una lista separada por comas: "3,5,8"
    $ids = implode(',', array_map('intval', array_keys($carrito)));
    
    $query = "SELECT * FROM Productos WHERE id IN ($ids)";
    $rs = $conn->query($query);
    
    $htmlArticulos = "<table style='width:100%; text-align:center; border-collapse:collapse; margin-bottom: 20px;'>";
    $htmlArticulos .= "<thead style='background:#eee;'><tr><th>Producto</th><th>Precio Ud.</th><th>Cantidad</th><th>Subtotal</th><th>Acciones</th></tr></thead><tbody>";
    
    $totalPedido = 0;
    
    while ($fila = $rs->fetch_assoc()) {
        $id = $fila['id'];
        $cantidad = $carrito[$id];
        
        // Calcular precios con IVA (el precio que se guardará en la base de datos después)
        $precioUd = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
        $subtotal = $precioUd * $cantidad;
        $totalPedido += $subtotal;
        
        $precioUdFmt = number_format($precioUd, 2, '.', '');
        $subtotalFmt = number_format($subtotal, 2, '.', '');
        
        $htmlArticulos .= "<tr>";
        $htmlArticulos .= "<td style='text-align: left; padding: 10px;'>{$fila['nombre']}</td>";
        $htmlArticulos .= "<td>{$precioUdFmt} €</td>";
        $htmlArticulos .= "<td><strong>{$cantidad}</strong></td>";
        $htmlArticulos .= "<td><strong>{$subtotalFmt} €</strong></td>";
        $htmlArticulos .= "<td>
            <form action='procesar_carrito.php' method='POST' style='display:inline;'>
                <input type='hidden' name='id_producto' value='{$id}'>
                <input type='hidden' name='accion' value='remove'>
                <button type='submit' style='background:none; border:none; color:red; cursor:pointer; text-decoration:underline;'>Quitar</button>
            </form>
        </td>";
        $htmlArticulos .= "</tr>";
    }
    
    $totalPedidoFmt = number_format($totalPedido, 2, '.', '');
    $htmlArticulos .= "</tbody></table>";
    $htmlArticulos .= "<h2 style='text-align:right; border-top: 2px solid #ccc; padding-top: 10px;'>Total a pagar: {$totalPedidoFmt} €</h2>";
    
    // 4. Formulario final: Tipo de pedido y Botón de Pago
    $htmlFormulario = <<<EOS
    <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ccc; margin-top: 20px; border-radius: 8px;">
        <h3>Opciones de entrega</h3>
        
        <form action="pago.php" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="cursor: pointer; font-size: 1.1em; font-weight: normal;">
                    <input type="radio" name="tipo_pedido" value="Local" required> 
                    🏪 Consumir en el local (Bistro FDI)
                </label>
                <br><br>
                <label style="cursor: pointer; font-size: 1.1em; font-weight: normal;">
                    <input type="radio" name="tipo_pedido" value="Llevar" required> 
                    🛍️ Para llevar
                </label>
            </div>
            
            <div style="display:flex; justify-content: space-between; align-items: center;">
                <a href="carta.php">Seguir comprando</a>
                <button type="submit" style="background-color:#28a745; color:white; padding:15px 30px; font-size:1.2em; border:none; cursor:pointer; border-radius: 5px; font-weight: bold;">
                    Ir al Pago 💳
                </button>
            </div>
        </form>
    </div>
    
    <div style="margin-top: 30px; text-align: center;">
        <form action="procesar_carrito.php" method="POST">
            <input type="hidden" name="accion" value="vaciar">
            <button type="submit" style="background:none; border:none; color:#dc3545; text-decoration:underline; cursor:pointer; font-size: 1em;">
                ⚠️ Cancelar Pedido (Vaciar carrito)
            </button>
        </form>
    </div>
EOS;

    $contenidoPrincipal = "<h1>Revisar Pedido</h1>" . $htmlArticulos . $htmlFormulario;
}

require 'includes/vistas/plantillas/plantilla.php';
?>