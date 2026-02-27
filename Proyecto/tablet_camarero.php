<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();
$esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;

if (!$esCamarero) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $idPed = (int)$_POST['id_pedido'];
    $nuevoEst = $conn->real_escape_string($_POST['nuevo_estado']);
    $conn->query("UPDATE Pedidos SET estado = '$nuevoEst' WHERE id = $idPed");
    header('Location: tablet_camarero.php');
    exit();
}

$tituloPagina = 'Tablet Camarero';

// Obtención de datos (Misma lógica)
$queryPedidos = "SELECT id, numero_pedido, tipo, total, estado FROM Pedidos 
                 WHERE estado IN ('Recibido', 'Listo cocina', 'Terminado') ORDER BY fecha ASC";
$rs = $conn->query($queryPedidos);

$pedidos = [];
$idsPedidos = [];
if ($rs && $rs->num_rows > 0) {
    while ($fila = $rs->fetch_assoc()) {
        $pedidos[$fila['id']] = $fila;
        $pedidos[$fila['id']]['productos'] = [];
        $idsPedidos[] = $fila['id'];
    }
}

if (!empty($idsPedidos)) {
    $idsStr = implode(',', $idsPedidos);
    $queryProds = "SELECT pp.id_pedido, pp.cantidad, pp.precio_unitario, p.nombre 
                   FROM pedidos_productos pp 
                   JOIN productos p ON pp.id_producto = p.id 
                   WHERE pp.id_pedido IN ($idsStr)";
    $rsProds = $conn->query($queryProds);
    if ($rsProds) {
        while ($p = $rsProds->fetch_assoc()) {
            $pedidos[$p['id_pedido']]['productos'][] = $p;
        }
    }
}

$nombreCamarero = $_SESSION['nombreUsuario'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'default.png'; 

$contenidoPrincipal = <<<EOS
    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #303030; color: white; padding: 10px 15px; border-radius: 8px; margin-bottom: 10px;">
        <h2 style="margin: 0; font-size: 1.2em;">Panel Camarero</h2>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 0.9em;"><strong>{$nombreCamarero}</strong></span>
            <img src="img/avatares/{$avatarCamarero}" alt="Avatar" style="width: 35px; height: 35px; border-radius: 50%; border: 2px solid white; object-fit: cover;">
        </div>
    </div>

    <div style="display: flex; gap: 10px; align-items: flex-start; width: 100%;">
EOS;

function generarTarjetaPedido($pedido, $botonTexto, $botonColor, $siguienteEstado) {
    $totalFmt = number_format($pedido['total'], 2, '.', '');
    
    // Lista de productos más compacta para que quepa en columnas estrechas
    $htmlProductos = '<div style="margin: 5px 0; font-size: 0.8em; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 5px 0;">';
    foreach ($pedido['productos'] as $prod) {
        $htmlProductos .= "<div style='display: flex; justify-content: space-between; gap: 5px;'>
            <span style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$prod['cantidad']}x {$prod['nombre']}</span>
            <span style='font-weight: bold;'>".number_format($prod['cantidad']*$prod['precio_unitario'],2)."€</span>
        </div>";
    }
    $htmlProductos .= '</div>';
    
    return <<<HTML
    <div style="background: white; border: 1px solid #ddd; border-radius: 6px; padding: 8px; margin-bottom: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9em;">
            <strong>#{$pedido['numero_pedido']}</strong>
            <span style="font-size: 0.75em; background: #f0f0f0; padding: 1px 4px; border-radius: 3px;">{$pedido['tipo']}</span>
        </div>
        {$htmlProductos}
        <div style="text-align: right; font-weight: bold; font-size: 0.9em; margin-bottom: 8px;">Total: {$totalFmt}€</div>
        <form action="tablet_camarero.php" method="POST">
            <input type="hidden" name="id_pedido" value="{$pedido['id']}">
            <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
            <button type="submit" style="width: 100%; padding: 8px; font-size: 0.85em; font-weight: bold; color: white; background-color: {$botonColor}; border: none; border-radius: 4px; cursor: pointer;">
                {$botonTexto}
            </button>
        </form>
    </div>
HTML;
}

$cols = ['Recibido' => '', 'Listo cocina' => '', 'Terminado' => ''];
foreach ($pedidos as $p) {
    if ($p['estado'] === 'Recibido') $cols['Recibido'] .= generarTarjetaPedido($p, 'Cobrar', '#ffc107', 'En preparacion');
    if ($p['estado'] === 'Listo cocina') $cols['Listo cocina'] .= generarTarjetaPedido($p, 'Preparar', '#17a2b8', 'Terminado');
    if ($p['estado'] === 'Terminado') $cols['Terminado'] .= generarTarjetaPedido($p, 'Entregar', '#28a745', 'Entregado');
}

$contenidoPrincipal .= <<<EOS
        <div style="flex: 1; background: #fff8e1; padding: 8px; border-radius: 8px; min-width: 0;">
            <h3 style="text-align: center; margin-top: 0; color: #856404; font-size: 1em; border-bottom: 1px solid #ffeeba;">Cobros</h3>
            {$cols['Recibido']}
        </div>
        <div style="flex: 1; background: #e0f7fa; padding: 8px; border-radius: 8px; min-width: 0;">
            <h3 style="text-align: center; margin-top: 0; color: #006064; font-size: 1em; border-bottom: 1px solid #bee5eb;">Cocina</h3>
            {$cols['Listo cocina']}
        </div>
        <div style="flex: 1; background: #e8f5e9; padding: 8px; border-radius: 8px; min-width: 0;">
            <h3 style="text-align: center; margin-top: 0; color: #1b5e20; font-size: 1em; border-bottom: 1px solid #c3e6cb;">Entrega</h3>
            {$cols['Terminado']}
        </div>
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';