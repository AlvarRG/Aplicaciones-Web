<?php
require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\pedidos\Pedido;

/**
 * Mapea el estado de un pedido o producto a su clase CSS y texto descriptivo.
 */
function getEstadoInfo($estado) {
    $info = [
        'clase' => 'badge-estado--generico',
        'texto' => $estado
    ];

    switch ($estado) {
        case 'Recibido':
            $info['clase'] = 'badge-estado--recibido';
            break;
        case 'En preparacion':
        case 'Cocinando':
            $info['clase'] = 'badge-estado--preparacion';
            $info['texto'] = ($estado === 'Cocinando') ? 'Cocinando' : 'Preparando';
            break;
        case 'Listo cocina':
            $info['clase'] = 'badge-estado--listo-cocina';
            $info['texto'] = 'Listo cocina';
            break;
        case 'Terminado':
        case 'Entregado':
            $info['clase'] = 'badge-estado--terminado';
            $info['texto'] = $estado;
            break;
        case 'Cancelado':
            $info['clase'] = 'badge-estado--cancelado';
            break;
    }
    return $info;
}

/**
 * Renderiza el badge de estado, incluyendo el avatar del cocinero si procede.
 */
function renderBadgeEstado($estado, $avatar = null, $rutaApp = "", $isMini = false) {
    $info = getEstadoInfo($estado);
    $style = $isMini ? "style='font-size:0.7em;'" : "";
    
    $html = "<span class='badge-estado {$info['clase']}' {$style}>{$info['texto']}</span>";
    
    if ($avatar && in_array($estado, ['En preparacion', 'Cocinando', 'Listo cocina'])) {
        $html .= "<div class='gestion-pedidos-avatar-wrapper'>
                    <img src='{$rutaApp}/img/avatares/{$avatar}' class='gestion-pedidos-avatar' title='Preparado por Chef'>
                  </div>";
    }
    
    return $html;
}

/**
 * Renderiza la tabla interna de productos que aparece al desplegar el pedido.
 */
function renderTablaProductosInterna($productos) {
    $html = "<table class='gestion-productos-interna'>";
    foreach ($productos as $prod) {
        $estadoP = $prod['estado'] ?? 'En preparacion';
        $badge = renderBadgeEstado($estadoP, null, "", true);
        
        $html .= "<tr>
            <td style='text-align:left; padding:4px;'>{$prod['cantidad']}x {$prod['nombre']}</td>
            <td style='text-align:right; padding:4px;'>{$badge}</td>
        </tr>";
    }
    $html .= "</table>";
    return $html;
}

/**
 * Renderiza una fila de la tabla de gestión de pedidos.
 */
function renderFilaPedido($fila, $rutaApp) {
    $id = $fila->getId();
    $numero = $fila->getNumeroPedido();
    $totalFmt = number_format($fila->getTotal(), 2, '.', '');
    $badgeEstado = renderBadgeEstado($fila->getEstado(), $fila->getAvatarCocinero(), $rutaApp);
    $htmlProductos = renderTablaProductosInterna($fila->getProductos());

    // Acción de cancelación
    $accion = "<span class='gestion-pedidos-sin-acciones'>Sin acciones</span>";
    if ($fila->getEstado() === 'Recibido') {
        $accion = "
            <form action='{$rutaApp}/gestion_pedidos.php' method='POST' class='form-inline'>
                <input type='hidden' name='id_pedido' value='{$id}'>
                <input type='hidden' name='accion' value='cancelar'>
                <button type='submit' class='btn-cancelar-pedido-admin'>Cancelar</button>
            </form>";
    }

    return <<<HTML
        <tr class='gestion-pedidos-row'>
            <td class='gestion-pedidos-cell gestion-pedidos-cell--numero'>
                <details>
                    <summary class="gestion-pedidos-summary">#{$numero}</summary>
                    <div class="gestion-pedidos-details">
                        $htmlProductos
                    </div>
                </details>
            </td>
            <td class='gestion-pedidos-cell'>{$fila->getFecha()}</td>
            <td class='gestion-pedidos-cell'>{$fila->getNombreCliente()}</td>
            <td class='gestion-pedidos-cell'>{$fila->getTipo()}</td>
            <td class='gestion-pedidos-cell gestion-pedidos-total'><strong>{$totalFmt} euros</strong></td>
            <td class='gestion-pedidos-cell'>{$badgeEstado}</td>
            <td class='gestion-pedidos-cell'>{$accion}</td>
        </tr>
HTML;
}

// --- LÓGICA DE CONTROL ---

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$esAdmin = $_SESSION['esAdmin'] ?? false;
$esCamarero = $_SESSION['esCamarero'] ?? false;
$esCocinero = $_SESSION['esCocinero'] ?? false;

if (!($esAdmin || $esCamarero || $esCocinero)) {
    header('Location: index.php');
    exit();
}

// Prioridad de nombre de rol
$nombreRol = "Personal";
if ($esCamarero) $nombreRol = "Camarero";
if ($esCocinero) $nombreRol = "Cocinero";
if ($esAdmin)    $nombreRol = "Gerente";

// Procesar cancelación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int) $_POST['id_pedido'];
    Pedido::cambiarEstado($idPed, 'Cancelado');
    header('Location: ' . RUTA_APP . '/gestion_pedidos.php');
    exit();
}

// Obtención de datos
$listaPedidos = Pedido::todosConCliente();
$idsPedidos = array_map(fn($p) => $p->getId(), $listaPedidos);

if (!empty($idsPedidos)) {
    $detalles = Pedido::detallesPedidos($idsPedidos);
    foreach ($listaPedidos as $fila) {
        $fila->setProductos($detalles[$fila->getId()] ?? []);
    }
}

// --- CONSTRUCCIÓN DE LA VISTA ---

$tituloPagina = 'Gestión Global de Pedidos';
$estilosExtra = ['gestion_pedidos.css'];
$rutaApp = RUTA_APP;

$contenidoPrincipal = <<<HTML
    <div class="gestion-header">
        <h1 class="gestion-header-title">Panel de Gestión de Pedidos</h1>
        <span class="gestion-header-rol">Vista: {$nombreRol}</span>
    </div>
HTML;

if (!empty($listaPedidos)) {
    $filasTabla = "";
    foreach ($listaPedidos as $fila) {
        $filasTabla .= renderFilaPedido($fila, $rutaApp);
    }

    $contenidoPrincipal .= <<<HTML
        <table class='gestion-pedidos-tabla'>
            <thead class='gestion-pedidos-thead'>
                <tr>
                    <th class='gestion-pedidos-th-principal'>Nº Pedido</th>
                    <th class='gestion-pedidos-th'>Fecha y Hora</th>
                    <th class='gestion-pedidos-th'>Cliente</th>
                    <th class='gestion-pedidos-th'>Tipo</th>
                    <th class='gestion-pedidos-th'>Total</th>
                    <th class='gestion-pedidos-th'>Estado</th>
                    <th class='gestion-pedidos-th'>Acciones</th>
                </tr>
            </thead>
            <tbody>$filasTabla</tbody>
        </table>
HTML;
} else {
    $contenidoPrincipal .= <<<HTML
        <div class='gestion-pedidos-empty'>
            <h3 class='gestion-pedidos-empty-title'>No hay pedidos registrados en el sistema todavía.</h3>
        </div>
HTML;
}

require __DIR__ . '/includes/vistas/plantillas/plantilla.php';