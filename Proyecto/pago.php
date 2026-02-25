<?php
require_once __DIR__.'/utils.php';
session_start();

// 1. SEGURIDAD: Usuario logueado y carrito con productos
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['carrito'])) {
    header('Location: carta.php');
    exit();
}

// Asumimos que guardas el ID del usuario en la sesión al hacer login
// (Asegúrate de que el nombre de esta variable coincide con tu login, suele ser 'id' o 'idUsuario')
$idUsuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 1; // Ponemos 1 de fallback por si acaso

$conn = conexionBD();

// 2. RECIBIR EL TIPO DE PEDIDO (De carrito.php)
if (isset($_POST['tipo_pedido'])) {
    $_SESSION['tipo_pedido'] = $_POST['tipo_pedido']; // Lo guardamos en sesión por si recarga la página
}
$tipoPedido = $_SESSION['tipo_pedido'] ?? 'Local'; // Por defecto Local

// 3. CALCULAR EL TOTAL REAL DESDE LA BASE DE DATOS (Por seguridad)
$ids = implode(',', array_map('intval', array_keys($_SESSION['carrito'])));
$queryTotal = "SELECT * FROM Productos WHERE id IN ($ids)";
$rsTotal = $conn->query($queryTotal);

$totalPedido = 0;
$productosDetalle = []; // Guardamos los datos para la inserción posterior

while ($fila = $rsTotal->fetch_assoc()) {
    $idProd = $fila['id'];
    $cantidad = $_SESSION['carrito'][$idProd];
    
    // Precio exacto con IVA en este momento
    $precioUdConIva = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
    $totalPedido += ($precioUdConIva * $cantidad);
    
    // Guardamos todo en un array temporal para insertarlo luego fácilmente
    $productosDetalle[] = [
        'id' => $idProd,
        'cantidad' => $cantidad,
        'precio_unitario' => $fila['precio_base'], // Guardamos la base sin IVA
        'iva' => $fila['iva']
    ];
}

// 4. PROCESAR EL PAGO Y GUARDAR EN LA BASE DE DATOS
if (isset($_POST['metodo_pago'])) {
    $metodoPago = $_POST['metodo_pago'];
    
    // Si paga con tarjeta, ya está pagado -> "En preparación"
    // Si paga al camarero -> "Recibido" (pendiente de cobro físico)
    $estadoInicial = ($metodoPago === 'tarjeta') ? 'En preparación' : 'Recibido';
    
    // A) Obtener el Número de Pedido Diario
    $queryNum = "SELECT IFNULL(MAX(numero_pedido), 0) + 1 AS nuevo_num 
                 FROM Pedidos 
                 WHERE DATE(fecha) = CURDATE()";
    $rsNum = $conn->query($queryNum);
    $filaNum = $rsNum->fetch_assoc();
    $numeroPedidoDiario = $filaNum['nuevo_num'];
    
    // B) Insertar en la tabla Pedidos (Cabecera)
    $queryPedido = sprintf("INSERT INTO Pedidos (id_usuario, numero_pedido, estado, tipo, total) 
                            VALUES (%d, %d, '%s', '%s', %F)",
        $idUsuario,
        $numeroPedidoDiario,
        $estadoInicial,
        $conn->real_escape_string($tipoPedido),
        $totalPedido
    );
    
    if ($conn->query($queryPedido)) {
        $idNuevoPedido = $conn->insert_id; // Obtenemos el ID autoincremental que le acaba de dar MySQL
        
        // C) Insertar en la tabla Pedidos_Productos (Detalle)
        foreach ($productosDetalle as $prod) {
            $queryDetalle = sprintf("INSERT INTO Pedidos_Productos (id_pedido, id_producto, cantidad, precio_unitario, iva) 
                                     VALUES (%d, %d, %d, %F, %d)",
                $idNuevoPedido,
                $prod['id'],
                $prod['cantidad'],
                $prod['precio_unitario'],
                $prod['iva']
            );
            $conn->query($queryDetalle);
        }
        
        // D) Vaciar el carrito porque el pedido ya es real
        unset($_SESSION['carrito']);
        unset($_SESSION['tipo_pedido']);
        
        // E) Redirigir a la pantalla de confirmación
        header("Location: confirmacion.php?pedido=$idNuevoPedido");
        exit();
    } else {
        $errorDB = "Hubo un problema al guardar el pedido: " . $conn->error;
    }
}

$tituloPagina = 'Pago del Pedido';
$totalPedidoFmt = number_format($totalPedido, 2, '.', '');

// 5. VISTA DEL FORMULARIO DE PAGO
$contenidoPrincipal = <<<EOS
    <h1>Pago de tu Pedido</h1>
    <div style="background: #eef9f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-size: 1.2em;">
        Total a pagar: <strong>{$totalPedidoFmt} €</strong><br>
        <small>Modo de entrega: {$tipoPedido}</small>
    </div>
EOS;

if (isset($errorDB)) {
    $contenidoPrincipal .= "<p style='color:red;'>$errorDB</p>";
}

$contenidoPrincipal .= <<<EOS
    <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
        
        <div style="border: 1px solid #ccc; padding: 20px; border-radius: 8px; width: 350px; background: white;">
            <h3>💳 Pagar con Tarjeta</h3>
            <p><small>Simulación: No se realizarán cargos reales.</small></p>
            
            <form action="pago.php" method="POST">
                <input type="hidden" name="metodo_pago" value="tarjeta">
                
                <label>Número de Tarjeta</label>
                <input type="text" name="tarjeta" required pattern="[\d\s\-]{16,19}" placeholder="1234 5678 9101 1121" style="width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box;">
                
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label>Caducidad</label>
                        <input type="text" name="caducidad" required pattern="(0[1-9]|1[0-2])\/\d{2}" placeholder="MM/YY" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>
                    <div style="flex: 1;">
                        <label>CVV</label>
                        <input type="text" name="cvv" required pattern="\d{3,4}" placeholder="123" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>
                </div>
                
                <button type="submit" style="background-color: #28a745; color: white; padding: 12px; width: 100%; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    Confirmar Pago y Pedir
                </button>
            </form>
        </div>

        <div style="border: 1px solid #ccc; padding: 20px; border-radius: 8px; width: 350px; background: white; text-align: center; display: flex; flex-direction: column; justify-content: center;">
            <h3>🤵 Pagar al Camarero</h3>
            <p>Prepararemos tu pedido y podrás abonarlo en efectivo o tarjeta cuando te atienda nuestro personal.</p>
            
            <form action="pago.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="metodo_pago" value="camarero">
                <button type="submit" style="background-color: #303030; color: white; padding: 12px; width: 100%; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    Pedir y Pagar al Camarero
                </button>
            </form>
        </div>

    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';