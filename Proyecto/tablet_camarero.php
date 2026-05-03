<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\pedidos\Pedido;

/**
 * Renderiza la cabecera del panel con la información del camarero.
 */
function renderHeaderCamarero($nombre, $avatar, $rutaImgs) {
    return <<<HTML
        <div class="tablet-camarero-header">
            <h2>Panel Camarero</h2>
            <div class="tablet-camarero-user">
                <span><strong>{$nombre}</strong></span>
                <img src="{$rutaImgs}/avatares/{$avatar}" alt="Avatar" class="tablet-camarero-avatar">
            </div>
        </div>
HTML;
}

/**
 * Renderiza una tarjeta individual de pedido para la tablet.
 */
function renderTarjetaPedido($pedido, $botonTexto, $botonClase, $siguienteEstado, $rutaApp) {
    $totalFmt = number_format($pedido->getTotal(), 2, '.', '');
    $htmlProductos = '<div class="tablet-camarero-productos">';
    
    foreach ($pedido->getProductos() as $prod) {
        $subtotal = number_format($prod['cantidad'] * $prod['precio_unitario'], 2);
        $htmlProductos .= "
            <div class='tablet-camarero-producto-row'>
                <span class='tablet-camarero-producto-nombre'>{$prod['cantidad']}x {$prod['nombre']}</span>
                <span class='tablet-camarero-producto-precio'>{$subtotal}€</span>
            </div>";
    }
    $htmlProductos .= '</div>';

    return <<<HTML
        <div class="tablet-camarero-card">
            <div class="tablet-camarero-card-header">
                <strong>#{$pedido->getNumeroPedido()}</strong>
                <span class="tablet-camarero-card-type">{$pedido->getTipo()}</span>
            </div>
            {$htmlProductos}
            <div class="tablet-camarero-total">Total: {$totalFmt}€</div>
            <form action="{$rutaApp}/tablet_camarero.php" method="POST">
                <input type="hidden" name="id_pedido" value="{$pedido->getId()}">
                <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
                <button type="submit" class="tablet-camarero-btn {$botonClase}">
                    {$botonTexto}
                </button>
            </form>
        </div>
HTML;
}

/**
 * Renderiza el layout principal de tres columnas.
 */
function renderLayoutTablet($columnas) {
    return <<<HTML
        <div class="tablet-camarero-layout">
            <div class="tablet-camarero-column tablet-camarero-column--cobros">
                <h3>Cobros</h3>
                {$columnas['Recibido']}
            </div>
            <div class="tablet-camarero-column tablet-camarero-column--cocina">
                <h3>Cocina</h3>
                {$columnas['Listo cocina']}
            </div>
            <div class="tablet-camarero-column tablet-camarero-column--entrega">
                <h3>Entrega</h3>
                {$columnas['Terminado']}
            </div>
        </div>
HTML;
}

// --- LÓGICA DE CONTROL ---

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$esCamarero = $_SESSION['esCamarero'] ?? false;
$esAdmin = $_SESSION['esAdmin'] ?? false;

if (!$esCamarero && !$esAdmin) {
    header('Location: index.php');
    exit();
}

$rutaApp = RUTA_APP;
$rutaImgs = RUTA_IMGS;

// Gestión de cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    Pedido::cambiarEstado((int)$_POST['id_pedido'], (string)$_POST['nuevo_estado']);
    header('Location: ' . $rutaApp . '/tablet_camarero.php');
    exit();
}

// Obtención y estructuración de datos
$listaPedidosRaw = Pedido::porEstados(['Recibido', 'Listo cocina', 'Terminado']);
$pedidos = [];
$idsPedidos = [];

foreach ($listaPedidosRaw as $p) {
    $p->setProductos([]);
    $pedidos[$p->getId()] = $p;
    $idsPedidos[] = $p->getId();
}

if (!empty($idsPedidos)) {
    $detalles = Pedido::detallesPedidos($idsPedidos);
    foreach ($detalles as $idPedido => $lineas) {
        foreach ($lineas as $l) {
            $pedidos[$idPedido]->addProducto($l);
        }
    }
}

// Configuración de la vista
$tituloPagina = 'Tablet Camarero';
$estilosExtra = ['tablet_camarero.css'];

$nombreCamarero = $_SESSION['nombreUsuario'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'default.png';

// Clasificación de pedidos en columnas
$cols = ['Recibido' => '', 'Listo cocina' => '', 'Terminado' => ''];
foreach ($pedidos as $p) {
    $estado = $p->getEstado();
    if ($estado === 'Recibido') {
        $cols['Recibido'] .= renderTarjetaPedido($p, 'Cobrar', 'tablet-camarero-btn--cobrar', 'En preparacion', $rutaApp);
    } elseif ($estado === 'Listo cocina') {
        $cols['Listo cocina'] .= renderTarjetaPedido($p, 'Preparar', 'tablet-camarero-btn--preparar', 'Terminado', $rutaApp);
    } elseif ($estado === 'Terminado') {
        $cols['Terminado'] .= renderTarjetaPedido($p, 'Entregar', 'tablet-camarero-btn--entregar', 'Entregado', $rutaApp);
    }
}

// Generación del contenido principal
$contenidoPrincipal = renderHeaderCamarero($nombreCamarero, $avatarCamarero, $rutaImgs);
$contenidoPrincipal .= renderLayoutTablet($cols);

require __DIR__.'/includes/vistas/plantillas/plantilla.php';