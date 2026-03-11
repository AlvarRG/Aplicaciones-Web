<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$estilosExtra = ['pago.css'];

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['carrito'])) {
    header('Location: carta.php');
    exit();
}

$idUsuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 1;

if (isset($_POST['tipo_pedido'])) {
    $_SESSION['tipo_pedido'] = $_POST['tipo_pedido'];
}
$tipoPedido = $_SESSION['tipo_pedido'] ?? 'Local';

$idsProductos = array_map('intval', array_keys($_SESSION['carrito']));
$placeholders = implode(',', array_fill(0, count($idsProductos), '?'));
$queryProductosCarrito = "SELECT * FROM Productos WHERE id IN ($placeholders)";
$tiposIdsProductos = str_repeat('i', count($idsProductos));
$rsTotal = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosCarrito, $tiposIdsProductos, ...$idsProductos)->get_result();

$totalPedido = 0;
$productosDetalle = [];

while ($fila = $rsTotal->fetch_assoc()) {
    $idProd = $fila['id'];
    $cantidad = $_SESSION['carrito'][$idProd];
    
    $precioUdConIva = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
    $totalPedido += ($precioUdConIva * $cantidad);
    
    $productosDetalle[] = [
        'id' => $idProd,
        'cantidad' => $cantidad,
        'precio_unitario' => $fila['precio_base'],
        'iva' => $fila['iva']
    ];
}
if ($rsTotal) {
    $rsTotal->free();
}

if (isset($_POST['metodo_pago'])) {
    $metodoPago = $_POST['metodo_pago'];
    
    $estadoInicial = ($metodoPago === 'tarjeta') ? 'En preparacion' : 'Recibido';
    
    $queryNuevoNumeroPedido = "SELECT IFNULL(MAX(numero_pedido), 0) + 1 AS nuevo_num 
                 FROM Pedidos 
                 WHERE DATE(fecha) = CURDATE()";
    $rsNum = Aplicacion::getInstance()->ejecutarConsultaBd($queryNuevoNumeroPedido)->get_result();
    $filaNum = $rsNum->fetch_assoc();
    $numeroPedidoDiario = $filaNum['nuevo_num'];
    if ($rsNum) {
        $rsNum->free();
    }
    
    $queryInsertPedido = "INSERT INTO Pedidos (id_usuario, numero_pedido, estado, tipo, total) VALUES (?, ?, ?, ?, ?)";
    Aplicacion::getInstance()->ejecutarConsultaBd(
        $queryInsertPedido,
        "iissd",
        (int)$idUsuario,
        (int)$numeroPedidoDiario,
        (string)$estadoInicial,
        (string)$tipoPedido,
        (float)$totalPedido
    );

    $idNuevoPedido = Aplicacion::getInstance()->getConexionBd()->insert_id;
    if ($idNuevoPedido) {
        
        foreach ($productosDetalle as $prod) {
            $queryInsertDetallePedido = "INSERT INTO Pedidos_Productos (id_pedido, id_producto, cantidad, precio_unitario, iva) VALUES (?, ?, ?, ?, ?)";
            Aplicacion::getInstance()->ejecutarConsultaBd(
                $queryInsertDetallePedido,
                "iiidi",
                (int)$idNuevoPedido,
                (int)$prod['id'],
                (int)$prod['cantidad'],
                (float)$prod['precio_unitario'],
                (int)$prod['iva']
            );
        }
        
        unset($_SESSION['carrito']);
        unset($_SESSION['tipo_pedido']);
        
        header("Location: confirmacion.php?pedido=$idNuevoPedido");
        exit();
    } else {
        $errorDB = "Hubo un problema al guardar el pedido.";
    }
}

$tituloPagina = 'Pago del Pedido';
$totalPedidoFmt = number_format($totalPedido, 2, '.', '');

$contenidoPrincipal = <<<EOS
    <h1>Pago de tu Pedido</h1>
    <div class="pago-resumen">
        Total a pagar: <strong>{$totalPedidoFmt} €</strong><br>
        <small>Modo de entrega: {$tipoPedido}</small>
    </div>
EOS;

if (isset($errorDB)) {
    $contenidoPrincipal .= "<p class='pago-error'>$errorDB</p>";
}

$contenidoPrincipal .= <<<EOS
    <div class="pago-tarjetas-wrapper">
        
        <div class="pago-tarjeta-form">
            <h3>Pagar con Tarjeta</h3>
            <p><small>Simulación: No se realizarán cargos reales.</small></p>
            
            <form action="pago.php" method="POST">
                <input type="hidden" name="metodo_pago" value="tarjeta">
                
                <label>Número de Tarjeta</label>
                <input type="text" name="tarjeta" required pattern="[\d\s\-]{16,19}" placeholder="1234 5678 9101 1121" class="pago-input-text">
                
                <div class="pago-input-row">
                    <div class="pago-input-col">
                        <label>Caducidad</label>
                        <input type="text" name="caducidad" required pattern="(0[1-9]|1[0-2])\/\d{2}" placeholder="MM/YY" class="pago-input-text">
                    </div>
                    <div class="pago-input-col">
                        <label>CVV</label>
                        <input type="text" name="cvv" required pattern="\d{3,4}" placeholder="123" class="pago-input-text">
                    </div>
                </div>
                
                <button type="submit" class="pago-boton-confirmar">
                    Confirmar Pago y Pedir
                </button>
            </form>
        </div>

        <div class="pago-camarero">
            <h3>Pagar al Camarero</h3>
            <p>Prepararemos tu pedido y podrás abonarlo en efectivo o tarjeta cuando te atienda nuestro personal.</p>
            
            <form action="pago.php" method="POST">
                <input type="hidden" name="metodo_pago" value="camarero">
                <button type="submit" class="pago-boton-camarero">
                    Pedir y Pagar al Camarero
                </button>
            </form>
        </div>

    </div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';