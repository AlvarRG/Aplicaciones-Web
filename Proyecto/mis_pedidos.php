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
 * Renderiza una fila individual de la tabla de pedidos.
 */
function renderFilaPedido($fila, $rutaApp) {
    $totalFmt = number_format($fila->getTotal(), 2, '.', '');
    $badgeEstado = renderBadgeEstado($fila->getEstado());
    
    $accion = "<span class='mis-pedidos-no-cancelable'>No cancelable</span>";
    
    // El cliente solo puede cancelar si el pedido aún no ha sido procesado (estado 'Recibido')
    if ($fila->getEstado() === 'Recibido') {
        $accion = <<<HTML
            <form action="{$rutaApp}/mis_pedidos.php" method="POST" class="form-inline form-cancelar-pedido-cliente" data-mensaje="¿Seguro que deseas cancelar tu pedido?">
                <input type="hidden" name="id_pedido" value="{$fila->getId()}">
                <input type="hidden" name="accion" value="cancelar">
                <button type="submit" class="btn-cancelar-pedido-cliente">Cancelar</button>
            </form>
HTML;
    }

    return <<<HTML
        <tr class="mis-pedidos-row">
            <td class="mis-pedidos-cell mis-pedidos-cell--numero">#{$fila->getNumeroPedido()}</td>
            <td class="mis-pedidos-cell">{$fila->getFecha()}</td>
            <td class="mis-pedidos-cell">{$fila->getTipo()}</td>
            <td class="mis-pedidos-cell mis-pedidos-total"><strong>{$totalFmt} €</strong></td>
            <td class="mis-pedidos-cell">{$badgeEstado}</td>
            <td class="mis-pedidos-cell">{$accion}</td>
        </tr>
HTML;
}

/**
 * Renderiza el contenido principal del historial de pedidos.
 */
function renderHistorialPedidos($pedidosUsuario, $rutaApp) {
    $html = "<h1>Historial de Mis Pedidos</h1>";
    $html .= "<p>Aquí puedes consultar el estado de tus pedidos y tu historial de compras.</p>";

    if (empty($pedidosUsuario)) {
        return $html . <<<HTML
            <div class="mis-pedidos-empty">
                <p>Aún no has realizado ningún pedido con nosotros.</p>
                <a href="{$rutaApp}/carta.php" class="mis-pedidos-empty-link">Ir a la Carta</a>
            </div>
HTML;
    }

    $filas = "";
    foreach ($pedidosUsuario as $fila) {
        $filas .= renderFilaPedido($fila, $rutaApp);
    }

    return $html . <<<HTML
        <div class="mis-pedidos-wrapper">
            <table class="mis-pedidos-tabla">
                <thead class="mis-pedidos-thead">
                    <tr>
                        <th class="mis-pedidos-th-principal">Nº Pedido</th>
                        <th class="mis-pedidos-th">Fecha</th>
                        <th class="mis-pedidos-th">Tipo</th>
                        <th class="mis-pedidos-th">Total</th>
                        <th class="mis-pedidos-th">Estado</th>
                        <th class="mis-pedidos-th">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {$filas}
                </tbody>
            </table>
        </div>
HTML;
}

// --- LÓGICA DE CONTROL ---

// Si el usuario no está logueado lo mandamos a login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$idUsuario = (int) $_SESSION['id'];
$rutaApp = RUTA_APP;

// Procesar la cancelación si se recibe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int) $_POST['id_pedido'];
    Pedido::cancelarCliente($idPed, $idUsuario);
    header('Location: ' . $rutaApp . '/mis_pedidos.php');
    exit();
}

// Configuración de la página
$tituloPagina = 'Mis Pedidos';
$estilosExtra = ['mis_pedidos.css'];
$scriptsExtra = ['confirmacion_cancelar_pedido.js'];

// Obtener datos
$pedidosUsuario = Pedido::porUsuario($idUsuario);

// Generar vista
$contenidoPrincipal = renderHistorialPedidos($pedidosUsuario, $rutaApp);

require __DIR__ . '/includes/vistas/plantillas/plantilla.php';