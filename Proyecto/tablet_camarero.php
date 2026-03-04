<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();
$esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;
$esAdmin = isset($_SESSION['esAdmin']) ? $_SESSION['esAdmin'] : false;

if (!$esCamarero && !$esAdmin) {
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
    <div class="tablet-camarero-header">
        <h2>Panel Camarero</h2>
        <div class="tablet-camarero-user">
            <span><strong>{$nombreCamarero}</strong></span>
            <img src="img/avatares/{$avatarCamarero}" alt="Avatar" class="tablet-camarero-avatar">
        </div>
    </div>

    <div class="tablet-camarero-layout">
EOS;

function generarTarjetaPedido($pedido, $botonTexto, $botonClase, $siguienteEstado) {
    $totalFmt = number_format($pedido['total'], 2, '.', '');
    
    // Lista de productos más compacta para que quepa en columnas estrechas
    $htmlProductos = '<div class="tablet-camarero-productos">';
    foreach ($pedido['productos'] as $prod) {
        $htmlProductos .= "<div class='tablet-camarero-producto-row'>
            <span class='tablet-camarero-producto-nombre'>{$prod['cantidad']}x {$prod['nombre']}</span>
            <span style='font-weight: bold;'>".number_format($prod['cantidad']*$prod['precio_unitario'],2)."€</span>
        </div>";
    }
    $htmlProductos .= '</div>';
    
    return <<<HTML
    <div class="tablet-camarero-card">
        <div class="tablet-camarero-card-header">
            <strong>#{$pedido['numero_pedido']}</strong>
            <span class="tablet-camarero-card-type">{$pedido['tipo']}</span>
        </div>
        {$htmlProductos}
        <div class="tablet-camarero-total">Total: {$totalFmt}€</div>
        <form action="tablet_camarero.php" method="POST">
            <input type="hidden" name="id_pedido" value="{$pedido['id']}">
            <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
            <button type="submit" class="tablet-camarero-btn {$botonClase}">
                {$botonTexto}
            </button>
        </form>
    </div>
HTML;
}

$cols = ['Recibido' => '', 'Listo cocina' => '', 'Terminado' => ''];
foreach ($pedidos as $p) {
    if ($p['estado'] === 'Recibido') $cols['Recibido'] .= generarTarjetaPedido($p, 'Cobrar', 'tablet-camarero-btn--cobrar', 'En preparacion');
    if ($p['estado'] === 'Listo cocina') $cols['Listo cocina'] .= generarTarjetaPedido($p, 'Preparar', 'tablet-camarero-btn--preparar', 'Terminado');
    if ($p['estado'] === 'Terminado') $cols['Terminado'] .= generarTarjetaPedido($p, 'Entregar', 'tablet-camarero-btn--entregar', 'Entregado');
}

$contenidoPrincipal .= <<<EOS
        <div class="tablet-camarero-column tablet-camarero-column--cobros">
            <h3>Cobros</h3>
            {$cols['Recibido']}
        </div>
        <div class="tablet-camarero-column tablet-camarero-column--cocina">
            <h3>Cocina</h3>
            {$cols['Listo cocina']}
        </div>
        <div class="tablet-camarero-column tablet-camarero-column--entrega">
            <h3>Entrega</h3>
            {$cols['Terminado']}
        </div>
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';