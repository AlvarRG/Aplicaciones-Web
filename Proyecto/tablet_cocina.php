<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();

$esCocinero = isset($_SESSION['esCocinero']) ? $_SESSION['esCocinero'] : false;
$esAdmin = isset($_SESSION['esAdmin']) ? $_SESSION['esAdmin'] : false;

if (!$esCocinero && !$esAdmin) {
    header('Location: index.php');
    exit();
}

// Procesar el cambio de estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $idPed = (int)$_POST['id_pedido'];
    $nuevoEst = $conn->real_escape_string($_POST['nuevo_estado']);
    
    $queryUpdate = "UPDATE Pedidos SET estado = '$nuevoEst' WHERE id = $idPed";
    $conn->query($queryUpdate);
    
    header('Location: tablet_cocina.php');
    exit();
}

$tituloPagina = 'Tablet Cocina';

// 1. Obtener los pedidos en curso
$queryPedidos = "SELECT id, numero_pedido, tipo, estado 
                 FROM Pedidos 
                 WHERE estado IN ('En preparacion', 'En preparación', 'Cocinando') 
                 ORDER BY fecha ASC";
$rs = $conn->query($queryPedidos);

$pedidos = [];
$idsPedidos = [];

if ($rs && $rs->num_rows > 0) {
    while ($fila = $rs->fetch_assoc()) {
        $pedidos[$fila['id']] = $fila;
        $pedidos[$fila['id']]['productos'] = []; // Preparamos el array para sus productos
        $idsPedidos[] = $fila['id'];
    }
}

// 2. Obtener los productos exactos de esos pedidos cruzando tablas
if (!empty($idsPedidos)) {
    $idsStr = implode(',', $idsPedidos);
    // Unimos pedidos_productos con productos para sacar el nombre del plato
    $queryProductos = "SELECT pp.id_pedido, pp.cantidad, p.nombre 
                       FROM pedidos_productos pp 
                       JOIN productos p ON pp.id_producto = p.id 
                       WHERE pp.id_pedido IN ($idsStr)";
    $rsProductos = $conn->query($queryProductos);
    
    if ($rsProductos) {
        while ($prod = $rsProductos->fetch_assoc()) {
            // Asignamos cada producto a su pedido correspondiente
            $pedidos[$prod['id_pedido']]['productos'][] = $prod;
        }
    }
}

$nombreCocinero = $_SESSION['nombreUsuario'] ?? 'Cocinero';
$avatarCocinero = $_SESSION['avatar'] ?? 'default.png'; 

$contenidoPrincipal = <<<EOS
    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #303030; color: white; padding: 15px 30px; border-radius: 10px; margin-bottom: 20px;">
        <h2 style="margin: 0;">Panel de Cocina</h2>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span style="font-size: 1.2em;">Chef <strong>{$nombreCocinero}</strong></span>
            <img src="img/avatares/{$avatarCocinero}" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid white; object-fit: cover;">
        </div>
    </div>

    <div style="display: flex; gap: 20px; overflow-x: auto; min-height: 500px;">
EOS;

// Función generadora de tarjetas adaptada para mostrar productos
function generarTarjetaPedidoCocina($pedido, $botonTexto, $botonColor, $siguienteEstado) {
    $textoTipo = ($pedido['tipo'] === 'Local') ? 'Local' : 'Llevar';
    
    // Generamos el HTML de la lista de productos
    $listaProductosHtml = '<ul style="list-style: none; padding: 0; margin: 15px 0; border-top: 1px dashed #ccc; padding-top: 10px; min-height: 80px;">';
    if (empty($pedido['productos'])) {
        $listaProductosHtml .= '<li style="color: #999; font-style: italic;">Sin detalles (Revisar carrito)</li>';
    } else {
        foreach ($pedido['productos'] as $prod) {
            $listaProductosHtml .= "<li style='font-size: 1.1em; padding: 5px 0;'><strong>{$prod['cantidad']}x</strong> {$prod['nombre']}</li>";
        }
    }
    $listaProductosHtml .= '</ul>';

    return <<<HTML
    <div style="background: white; border: 2px solid #ccc; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
            <strong style="font-size: 1.5em;">#{$pedido['numero_pedido']}</strong>
            <span style="font-size: 1.1em; color: #555;">{$textoTipo}</span>
        </div>
        
        {$listaProductosHtml}
        
        <form action="tablet_cocina.php" method="POST">
            <input type="hidden" name="id_pedido" value="{$pedido['id']}">
            <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
            <button type="submit" style="width: 100%; padding: 15px; font-size: 1.1em; font-weight: bold; color: white; background-color: {$botonColor}; border: none; border-radius: 5px; cursor: pointer; margin-top: 5px;">
                {$botonTexto}
            </button>
        </form>
    </div>
HTML;
}

$htmlEnPreparacion = "";
$htmlCocinando = "";

// Clasificamos los pedidos en sus columnas correspondientes
foreach ($pedidos as $pedido) {
    if ($pedido['estado'] === 'En preparacion' || $pedido['estado'] === 'En preparación') {
        $htmlEnPreparacion .= generarTarjetaPedidoCocina($pedido, 'Empezar a Cocinar', '#fd7e14', 'Cocinando');
    } elseif ($pedido['estado'] === 'Cocinando') {
        $htmlCocinando .= generarTarjetaPedidoCocina($pedido, 'Marcar Listo', '#28a745', 'Listo cocina');
    }
}

$contenidoPrincipal .= <<<EOS
        <div style="flex: 1; background: #f8f9fa; padding: 15px; border-radius: 10px; min-width: 300px;">
            <h3 style="text-align: center; color: #856404; border-bottom: 2px solid #ffeeba; padding-bottom: 10px;">Comandas Nuevas</h3>
            {$htmlEnPreparacion}
        </div>

        <div style="flex: 1; background: #f8f9fa; padding: 15px; border-radius: 10px; min-width: 300px;">
            <h3 style="text-align: center; color: #0c5460; border-bottom: 2px solid #bee5eb; padding-bottom: 10px;">En los Fuegos</h3>
            {$htmlCocinando}
        </div>
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';
?>