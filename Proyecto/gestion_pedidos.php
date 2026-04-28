<?php
require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\pedidos\Pedido;


//Redirigimos si el usuario no ha iniciado sesión
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

//Leemos los roles del usuario desde la sesión
$esAdmin = isset($_SESSION['esAdmin']) ? $_SESSION['esAdmin'] : false;
$esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;
$esCocinero = isset($_SESSION['esCocinero']) ? $_SESSION['esCocinero'] : false;

//Solo el personal del restaurante puede acceder a esta página
$esPersonal = $esAdmin || $esCamarero || $esCocinero;

if (!$esPersonal) {
    header('Location: index.php');
    exit();
}

//Determinamos el nombre del rol para mostrarlo en la vista (el más prioritario gana)
$nombreRol = "Personal";
if ($esCamarero)
    $nombreRol = "Camarero";
if ($esCocinero)
    $nombreRol = "Cocinero";
if ($esAdmin)
    $nombreRol = "Gerente";

//Procesamos la acción de cancelar pedido si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int) $_POST['id_pedido'];

    //Solo se puede cancelar un pedido si está en estado 'Recibido'
    Pedido::cambiarEstado($idPed, 'Cancelado');

    //Redirigimos para evitar reenvío del formulario al refrescar
    header('Location: ' . RUTA_APP . '/gestion_pedidos.php');
    exit();
}

//Parámetros para la plantilla
$estilosExtra = ['gestion_pedidos.css'];

$tituloPagina = 'Gestion Global de Pedidos';
$rutaApp = RUTA_APP;

//Obtenemos todos los pedidos junto con el nombre del cliente, ordenados por fecha
$listaPedidos = Pedido::todosConCliente();

$idsPedidos = [];
if (!empty($listaPedidos)) {
    foreach ($listaPedidos as $fila) {
        $fila->setProductos([]);
        $idsPedidos[] = $fila->getId();
    }
}

if (!empty($idsPedidos)) {
    $detalles = Pedido::detallesPedidos($idsPedidos);
    foreach ($listaPedidos as $fila) {
        if (isset($detalles[$fila->getId()])) {
            $fila->setProductos($detalles[$fila->getId()]);
        }
    }
}

//Cabecera de la página con el rol del usuario actual
$contenidoPrincipal = <<<EOS
    <div class="gestion-header">
        <h1 class="gestion-header-title">Panel de Gestion de Pedidos</h1>
        <span class="gestion-header-rol">Vista: {$nombreRol}</span>
    </div>
EOS;

//Construimos las filas de la tabla (solo la parte variable)
if (!empty($listaPedidos)) {
    $filasTabla = "";
    foreach ($listaPedidos as $fila) {
        $totalFmt = number_format($fila->getTotal(), 2, '.', '');

        //Clase CSS del badge según el estado del pedido
        $claseEstado = 'badge-estado--generico';
        $textoBadgeGlobal = $fila->getEstado();

        switch ($fila->getEstado()) {
            case 'Recibido':
                $claseEstado = 'badge-estado--recibido';
                break;
            case 'En preparacion':
            case 'Cocinando':
                $claseEstado = 'badge-estado--preparacion';
                $textoBadgeGlobal = 'Preparando';
                if ($fila->getEstado() === 'Cocinando')
                    $textoBadgeGlobal = 'Cocinando';
                break;
            case 'Listo cocina':
                $claseEstado = 'badge-estado--listo-cocina';
                $textoBadgeGlobal = 'Listo cocina';
                break;
            case 'Terminado':
                $claseEstado = 'badge-estado--terminado';
                $textoBadgeGlobal = 'Terminado';
                break;
            case 'Entregado':
                $claseEstado = 'badge-estado--terminado';
                $textoBadgeGlobal = 'Entregado';
                break;
            case 'Cancelado':
                $claseEstado = 'badge-estado--cancelado';
                break;
        }

        $badgeEstado = "<span class='badge-estado {$claseEstado}'>{$textoBadgeGlobal}</span>";
        if ($fila->getAvatarCocinero() && in_array($fila->getEstado(), ['En preparacion', 'Cocinando', 'Listo cocina'])) {
            $badgeEstado .= "<div class='gestion-pedidos-avatar-wrapper'><img src='{$rutaApp}/img/avatares/{$fila->getAvatarCocinero()}' class='gestion-pedidos-avatar' title='Preparado por Chef'></div>";
        }

        //Columna de acciones: solo los pedidos 'Recibido' se pueden cancelar
        if ($fila->getEstado() === 'Recibido') {
            $accion = "
                <form action='$rutaApp/gestion_pedidos.php' method='POST' class='form-inline'>
                    <input type='hidden' name='id_pedido' value='{$fila->getId()}'>
                    <input type='hidden' name='accion' value='cancelar'>
                    <button type='submit' class='btn-cancelar-pedido-admin'>Cancelar</button>
                </form>";
        } else {
            $accion = "<span class='gestion-pedidos-sin-acciones'>Sin acciones</span>";
        }

        // Listado de productos interno (Details HTML5)
        $htmlProductos = "<table class='gestion-productos-interna'>";
        foreach ($fila->getProductos() as $prod) {
            $estadoP = $prod['estado'] ?? 'En preparacion';
            $badgeProd = 'badge-estado--generico';
            $textoBadgeProd = $estadoP;

            switch ($estadoP) {
                case 'Recibido':
                    $badgeProd = 'badge-estado--recibido';
                    break;
                case 'En preparacion':
                case 'Cocinando':
                    $badgeProd = 'badge-estado--preparacion';
                    $textoBadgeProd = 'Preparando';
                    if ($estadoP === 'Cocinando')
                        $textoBadgeProd = 'Cocinando';
                    break;
                case 'Listo cocina':
                    $badgeProd = 'badge-estado--listo-cocina';
                    $textoBadgeProd = 'Listo cocina';
                    break;
                case 'Terminado':
                    $badgeProd = 'badge-estado--terminado';
                    $textoBadgeProd = 'Terminado';
                    break;
                case 'Entregado':
                    $badgeProd = 'badge-estado--terminado';
                    $textoBadgeProd = 'Entregado';
                    break;
                case 'Cancelado':
                    $badgeProd = 'badge-estado--cancelado';
                    break;
            }
            $htmlProductos .= "<tr>
                <td style='text-align:left; padding:4px;'>{$prod['cantidad']}x {$prod['nombre']}</td>
                <td style='text-align:right; padding:4px;'><span class='badge-estado {$badgeProd}' style='font-size:0.7em;'>{$textoBadgeProd}</span></td>
            </tr>";
        }
        $htmlProductos .= "</table>";

        $filasTabla .= <<<EOS
            <tr class='gestion-pedidos-row'>
                <td class='gestion-pedidos-cell gestion-pedidos-cell--numero'>
                    <details>
                        <summary class="gestion-pedidos-summary">#{$fila->getNumeroPedido()}</summary>
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
        EOS;
    }

    //Tabla completa con las filas construidas
    $contenidoPrincipal .= <<<EOS
        <table class='gestion-pedidos-tabla'>
            <thead class='gestion-pedidos-thead'>
                <tr>
                    <th class='gestion-pedidos-th-principal'>N Pedido</th>
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
    EOS;
} else {
    //Mensaje si no hay ningún pedido en el sistema
    $contenidoPrincipal .= <<<EOS
        <div class='gestion-pedidos-empty'>
            <h3 class='gestion-pedidos-empty-title'>No hay pedidos registrados en el sistema todavia.</h3>
        </div>
    EOS;
}

require __DIR__ . '/includes/vistas/plantillas/plantilla.php';