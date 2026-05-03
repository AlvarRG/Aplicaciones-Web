<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\pedidos\Pedido;

/**
 * Renderiza la cabecera del panel con la información del cocinero.
 */
function renderHeaderCocina($nombre, $avatar, $rutaImgs) {
    return <<<HTML
        <div class="tablet-cocinero-header">
            <h2>Panel Cocina</h2>
            <div class="tablet-camarero-user">
                <span>Chef: <strong>{$nombre}</strong></span>
                <img src="{$rutaImgs}/avatares/{$avatar}" alt="Avatar" class="tablet-camarero-avatar">
            </div>
        </div>
HTML;
}

/**
 * Renderiza la fila de un producto individual dentro de la tarjeta de pedido.
 */
function renderProductoCocina($idPedido, $prod, $esCocinando, $rutaApp) {
    $estadoP = $prod['estado'] ?? 'En preparacion';
    $isDone = ($estadoP === 'Listo cocina' || $estadoP === 'Terminado');
    $tachado = $isDone ? 'text-decoration: line-through; color: #aaa;' : '';
    
    $formProducto = "";
    
    if ($esCocinando && !$isDone) {
        $formProducto = <<<HTML
            <form action="{$rutaApp}/tablet_cocina.php" method="POST" style="display:inline; margin:0;">
                <input type="hidden" name="id_pedido" value="{$idPedido}">
                <input type="hidden" name="id_producto" value="{$prod['id_producto']}">
                <input type="hidden" name="nuevo_estado_producto" value="Listo cocina">
                <button type="submit" class="tablet-cocinero-btn--mini">Listo</button>
            </form>
HTML;
    } elseif ($isDone) {
        $formProducto = "<span style='color:#28a745; font-weight:bold;'>✔</span>";
    }

    return <<<HTML
        <div class="tablet-cocinero-producto-row" style="display:flex; justify-content:space-between; align-items:center;">
            <span style="flex:1; margin-right:10px; {$tachado}"><strong>{$prod['cantidad']}x</strong> {$prod['nombre']}</span>
            <div>{$formProducto}</div>
        </div>
HTML;
}

/**
 * Renderiza una tarjeta individual de pedido para la cocina.
 */
function renderTarjetaCocina($pedido, $botonTexto, $claseBoton, $siguienteEstado, $rutaApp) {
    $esCocinando = ($pedido->getEstado() === 'Cocinando');
    $htmlProductos = "";

    foreach ($pedido->getProductos() as $prod) {
        $htmlProductos .= renderProductoCocina($pedido->getId(), $prod, $esCocinando, $rutaApp);
    }

    $textoFooterBoton = $esCocinando ? "{$botonTexto} TODO" : $botonTexto;

    return <<<HTML
        <div class="tablet-cocinero-card">
            <div class="tablet-cocinero-card-header">
                <span>#{$pedido->getNumeroPedido()}</span>
                <span class="tablet-camarero-card-type">{$pedido->getTipo()}</span>
            </div>
            <div class="tablet-cocinero-productos">
                {$htmlProductos}
            </div>
            <form action="{$rutaApp}/tablet_cocina.php" method="POST">
                <input type="hidden" name="id_pedido" value="{$pedido->getId()}">
                <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
                <button type="submit" class="tablet-cocinero-btn {$claseBoton}">
                    {$textoFooterBoton}
                </button>
            </form>
        </div>
HTML;
}

/**
 * Renderiza el layout principal de dos columnas.
 */
function renderLayoutCocina($colNuevas, $colProceso) {
    return <<<HTML
        <div class="tablet-cocinero-layout">
            <div class="tablet-cocinero-column tablet-cocinero-column--nuevas">
                <h3>Nuevas Comandas</h3>
                {$colNuevas}
            </div>
            <div class="tablet-cocinero-column tablet-cocinero-column--proceso">
                <h3>En los Fuegos</h3>
                {$colProceso}
            </div>
        </div>
HTML;
}

// --- LÓGICA DE CONTROL ---

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$esCocinero = $_SESSION['esCocinero'] ?? false;
$esAdmin = $_SESSION['esAdmin'] ?? false;

if (!$esCocinero && !$esAdmin) {
    header('Location: index.php');
    exit();
}

$rutaApp = RUTA_APP;
$rutaImgs = RUTA_IMGS;

// Gestión de cambio de estado en los pedidos y productos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_pedido'], $_POST['nuevo_estado']) && !isset($_POST['id_producto'])) {
        $idPed = (int)$_POST['id_pedido'];
        $nuevoEst = (string)$_POST['nuevo_estado'];
        
        if ($nuevoEst === 'Cocinando') {
            Pedido::asignarCocineroYEstado($idPed, $_SESSION['id'], $nuevoEst);
        } else {
            Pedido::cambiarEstado($idPed, $nuevoEst);
        }
        header('Location: ' . $rutaApp . '/tablet_cocina.php');
        exit();
        
    } elseif (isset($_POST['id_pedido'], $_POST['id_producto'], $_POST['nuevo_estado_producto'])) {
        $idPed = (int)$_POST['id_pedido'];
        $idProd = (int)$_POST['id_producto'];
        $nuevoEstProd = (string)$_POST['nuevo_estado_producto'];
        
        Pedido::cambiarEstadoProducto($idPed, $idProd, $nuevoEstProd);
        header('Location: ' . $rutaApp . '/tablet_cocina.php');
        exit();
    }
}

// Obtención y estructuración de datos
$listaPedidos = Pedido::porEstados(['En preparacion', 'Cocinando']);
$pedidos = [];
$idsPedidos = [];

if (!empty($listaPedidos)) {
    foreach ($listaPedidos as $fila) {
        $fila->setProductos([]);
        $pedidos[$fila->getId()] = $fila;
        $idsPedidos[] = $fila->getId();
    }
}

if (!empty($idsPedidos)) {
    $detalles = Pedido::detallesPedidos($idsPedidos);
    foreach ($detalles as $idPedido => $lineas) {
        foreach ($lineas as $prod) {
            $pedidos[$idPedido]->addProducto($prod);
        }
    }
}

// Configuración de la vista
$tituloPagina = 'Tablet Cocina';
$estilosExtra = ['tablet_cocina.css'];

$nombreCocinero = $_SESSION['nombreUsuario'] ?? 'Chef';
$avatarCocinero = $_SESSION['avatar'] ?? 'default.png';

// Clasificación de pedidos en columnas
$colNuevas = "";
$colProceso = "";

foreach ($pedidos as $p) {
    if ($p->getEstado() === 'En preparacion') {
        $colNuevas .= renderTarjetaCocina($p, 'COCINAR', 'tablet-cocinero-btn--cocinar', 'Cocinando', $rutaApp);
    } elseif ($p->getEstado() === 'Cocinando') {
        $colProceso .= renderTarjetaCocina($p, 'LISTO', 'tablet-cocinero-btn--listo', 'Listo cocina', $rutaApp);
    }
}

// Generación del contenido principal
$contenidoPrincipal = renderHeaderCocina($nombreCocinero, $avatarCocinero, $rutaImgs);
$contenidoPrincipal .= renderLayoutCocina($colNuevas, $colProceso);

require __DIR__.'/includes/vistas/plantillas/plantilla.php';