<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Pedido;

//Si el usuario no está logeado lo mandamos a login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

//Cogemos el id del usuario
$idUsuario = (int)$_SESSION['id'];

//Cancelar el pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int)$_POST['id_pedido'];
    
    Pedido::cancelarCliente($idPed, $idUsuario);
    
    header('Location: mis_pedidos.php');
    exit();
}

$tituloPagina = 'Mis Pedidos';
$estilosExtra = $estilosExtra ?? [];
$scriptsExtra = $scriptsExtra ?? [];
$scriptsExtra[] = 'confirmacion_cancelar_pedido.js';

$pedidosUsuario = Pedido::porUsuario($idUsuario);

$contenidoPrincipal = <<<EOS
    <h1>Historial de Mis Pedidos</h1>
    <p>Aquí puedes consultar el estado de tus pedidos y tu historial de compras.</p>
EOS;

if (!empty($pedidosUsuario)) {
    $contenidoPrincipal .= "<table class='mis-pedidos-tabla'>";
    $contenidoPrincipal .= "<thead class='mis-pedidos-thead'>
        <tr>
            <th class='mis-pedidos-th-principal'>Nº Pedido</th>
            <th class='mis-pedidos-th'>Fecha</th>
            <th class='mis-pedidos-th'>Tipo</th>
            <th class='mis-pedidos-th'>Total</th>
            <th class='mis-pedidos-th'>Estado</th>
            <th class='mis-pedidos-th'>Acciones</th>
        </tr>
    </thead><tbody>";
    
    foreach ($pedidosUsuario as $fila) {
        $totalFmt = number_format($fila['total'], 2, '.', '');
        
        $claseEstado = 'badge-estado--generico'; 
        switch ($fila['estado']) {
            case 'Recibido': $claseEstado = 'badge-estado--recibido'; break; 
            case 'En preparacion':
            case 'Cocinando': $claseEstado = 'badge-estado--preparacion'; break; 
            case 'Listo cocina': $claseEstado = 'badge-estado--listo-cocina'; break; 
            case 'Terminado': 
            case 'Entregado': $claseEstado = 'badge-estado--terminado'; break; 
            case 'Cancelado': $claseEstado = 'badge-estado--cancelado'; break; 
        }
        
        $badgeEstado = "<span class='badge-estado {$claseEstado}'>{$fila['estado']}</span>";
        
        $contenidoPrincipal .= "<tr class='mis-pedidos-row'>";
        $contenidoPrincipal .= "<td class='mis-pedidos-cell mis-pedidos-cell--numero'>#{$fila['numero_pedido']}</td>";
        $contenidoPrincipal .= "<td class='mis-pedidos-cell'>{$fila['fecha']}</td>";
        $contenidoPrincipal .= "<td class='mis-pedidos-cell'>{$fila['tipo']}</td>";
        $contenidoPrincipal .= "<td class='mis-pedidos-cell mis-pedidos-total'><strong>{$totalFmt} €</strong></td>";
        $contenidoPrincipal .= "<td class='mis-pedidos-cell'>{$badgeEstado}</td>";
        
        $contenidoPrincipal .= "<td class='mis-pedidos-cell'>";
        //Cancelar el pedido, solo si está en estado 'Recibido'
        if ($fila['estado'] === 'Recibido') {
            $contenidoPrincipal .= "
                <form action='mis_pedidos.php' method='POST' class='form-inline form-cancelar-pedido-cliente' data-mensaje='¿Seguro que deseas cancelar tu pedido?'>
                    <input type='hidden' name='id_pedido' value='{$fila['id']}'>
                    <input type='hidden' name='accion' value='cancelar'>
                    <button type='submit' class='btn-cancelar-pedido-cliente'>Cancelar</button>
                </form>
            ";
        } else {
            $contenidoPrincipal .= "<span class='mis-pedidos-no-cancelable'>No cancelable</span>";
        }
        $contenidoPrincipal .= "</td>";
        $contenidoPrincipal .= "</tr>";
    }
    
    $contenidoPrincipal .= "</tbody></table>";
} else {
    $contenidoPrincipal .= "<div class='mis-pedidos-empty'>
        <p>Aún no has realizado ningún pedido con nosotros.</p>
        <a href='carta.php' class='mis-pedidos-empty-link'>Ir a la Carta</a>
    </div>";
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';