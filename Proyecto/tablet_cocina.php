<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();
$esCocinero = $_SESSION['esCocinero'] ?? false;
$esAdmin = $_SESSION['esAdmin'] ?? false;

if (!$esCocinero && !$esAdmin) {
    header('Location: index.php');
    exit();
}

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $idPed = (int)$_POST['id_pedido'];
    $nuevoEst = $conn->real_escape_string($_POST['nuevo_estado']);
    $conn->query("UPDATE pedidos SET estado = '$nuevoEst' WHERE id = $idPed");
    header('Location: tablet_cocina.php');
    exit();
}

$tituloPagina = 'Tablet Cocina';
$estilosExtra = ['tablet_cocina.css'];

// Consulta de pedidos (usando el nombre correcto de tu tabla: pedidos_productos)
$queryPedidos = "SELECT id, numero_pedido, tipo, estado FROM pedidos 
                 WHERE estado IN ('En preparación', 'Cocinando') ORDER BY fecha ASC";
$rs = $conn->query($queryPedidos);

$pedidos = [];
if ($rs) {
    while ($p = $rs->fetch_assoc()) {
        $idPed = $p['id'];
        $resProds = $conn->query("SELECT pp.cantidad, p.nombre FROM pedidos_productos pp 
                                  JOIN productos p ON pp.id_producto = p.id 
                                  WHERE pp.id_pedido = $idPed");
        $p['productos'] = $resProds->fetch_all(MYSQLI_ASSOC);
        $pedidos[] = $p;
    }
}

function generarTarjetaCocina($pedido, $botonTexto, $claseBoton, $siguienteEstado) {
    $htmlProductos = "";
    foreach ($pedido['productos'] as $prod) {
        $htmlProductos .= "<div class='tablet-cocinero-producto-row'>
                                <span><strong>{$prod['cantidad']}x</strong> {$prod['nombre']}</span>
                           </div>";
    }

    return <<<HTML
    <div class="tablet-cocinero-card">
        <div class="tablet-cocinero-card-header">
            <span>#{$pedido['numero_pedido']}</span>
            <span class="tablet-camarero-card-type">{$pedido['tipo']}</span>
        </div>
        <div class="tablet-cocinero-productos">
            $htmlProductos
        </div>
        <form action="tablet_cocina.php" method="POST">
            <input type="hidden" name="id_pedido" value="{$pedido['id']}">
            <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
            <button type="submit" class="tablet-cocinero-btn {$claseBoton}">
                $botonTexto
            </button>
        </form>
    </div>
HTML;
}

$colNuevas = "";
$colProceso = "";

foreach ($pedidos as $p) {
    // Importante: Chequeamos el estado con acento como está en tu SQL
    if ($p['estado'] === 'En preparación') {
        $colNuevas .= generarTarjetaCocina($p, 'COCINAR', 'tablet-cocinero-btn--cocinar', 'Cocinando');
    } elseif ($p['estado'] === 'Cocinando') {
        $colProceso .= generarTarjetaCocina($p, 'LISTO', 'tablet-cocinero-btn--listo', 'Listo cocina');
    }
}

$nombreUser = $_SESSION['nombreUsuario'] ?? 'Chef';
$avatar = $_SESSION['avatar'] ?? 'default.png';

$contenidoPrincipal = <<<EOS
<div class="tablet-cocinero-header">
    <h2>Panel Cocina</h2>
    <div class="tablet-camarero-user">
        <span>Chef: <strong>$nombreUser</strong></span>
        <img src="img/avatares/$avatar" alt="Avatar" class="tablet-camarero-avatar">
    </div>
</div>

<div class="tablet-cocinero-layout">
    <div class="tablet-cocinero-column tablet-cocinero-column--nuevas">
        <h3>Nuevas Comandas</h3>
        $colNuevas
    </div>
    <div class="tablet-cocinero-column tablet-cocinero-column--proceso">
        <h3>En los Fuegos</h3>
        $colProceso
    </div>
</div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';