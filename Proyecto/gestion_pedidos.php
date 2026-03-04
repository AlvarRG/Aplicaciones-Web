<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();

$esAdmin = isset($_SESSION['esAdmin']) ? $_SESSION['esAdmin'] : false;
$esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;
$esCocinero = isset($_SESSION['esCocinero']) ? $_SESSION['esCocinero'] : false;

$esPersonal = $esAdmin || $esCamarero || $esCocinero;

if (!$esPersonal) {
    header('Location: index.php');
    exit();
}

$nombreRol = "Personal";
if ($esCamarero) $nombreRol = "Camarero";
if ($esCocinero) $nombreRol = "Cocinero";
if ($esAdmin) $nombreRol = "Gerente";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int)$_POST['id_pedido'];
    
    $queryCheck = "SELECT estado FROM Pedidos WHERE id = $idPed";
    $rsCheck = $conn->query($queryCheck);
    
    if ($rsCheck && $rsCheck->num_rows > 0) {
        $estadoActual = $rsCheck->fetch_assoc()['estado'];
        if ($estadoActual === 'Recibido') {
            $queryCancel = "UPDATE Pedidos SET estado = 'Cancelado' WHERE id = $idPed";
            $conn->query($queryCancel);
        }
    }
    
    header('Location: gestion_pedidos.php');
    exit();
}

$tituloPagina = 'Gestion Global de Pedidos';
$estilosExtra = ['gestion_pedidos.css'];

$query = "SELECT p.id, p.numero_pedido, p.fecha, p.estado, p.tipo, p.total, u.nombre AS nombre_cliente 
          FROM Pedidos p 
          JOIN usuarios u ON p.id_usuario = u.id 
          ORDER BY p.fecha DESC";
$rs = $conn->query($query);

$contenidoPrincipal = <<<EOS
    <div class="gestion-header">
        <h1 class="gestion-header-title">Panel de Gestion de Pedidos</h1>
        <span class="gestion-header-rol">Vista: {$nombreRol}</span>
    </div>
EOS;

if ($rs && $rs->num_rows > 0) {
    $contenidoPrincipal .= "<table class='gestion-pedidos-tabla'>";
    $contenidoPrincipal .= "<thead class='gestion-pedidos-thead'>
        <tr>
            <th class='gestion-pedidos-th-principal'>N Pedido</th>
            <th class='gestion-pedidos-th'>Fecha y Hora</th>
            <th class='gestion-pedidos-th'>Cliente</th>
            <th class='gestion-pedidos-th'>Tipo</th>
            <th class='gestion-pedidos-th'>Total</th>
            <th class='gestion-pedidos-th'>Estado</th>
            <th class='gestion-pedidos-th'>Acciones</th>
        </tr>
    </thead><tbody>";
    
    while ($fila = $rs->fetch_assoc()) {
        $totalFmt = number_format($fila['total'], 2, '.', '');
        
        $claseEstado = 'badge-estado--generico'; 
        switch ($fila['estado']) {
            case 'Recibido': $claseEstado = 'badge-estado--recibido'; break; 
            case 'En preparacion': 
            case 'En preparación':
            case 'Cocinando': $claseEstado = 'badge-estado--preparacion'; break; 
            case 'Listo cocina': $claseEstado = 'badge-estado--listo-cocina'; break; 
            case 'Terminado': 
            case 'Entregado': $claseEstado = 'badge-estado--terminado'; break; 
            case 'Cancelado': $claseEstado = 'badge-estado--cancelado'; break; 
        }
        
        $badgeEstado = "<span class='badge-estado {$claseEstado}'>{$fila['estado']}</span>";
        
        $contenidoPrincipal .= "<tr class='gestion-pedidos-row'>";
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell gestion-pedidos-cell--numero'>#{$fila['numero_pedido']}</td>";
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell'>{$fila['fecha']}</td>";
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell'>{$fila['nombre_cliente']}</td>";
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell'>{$fila['tipo']}</td>";
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell gestion-pedidos-total'><strong>{$totalFmt} euros</strong></td>";
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell'>{$badgeEstado}</td>";
        
        $contenidoPrincipal .= "<td class='gestion-pedidos-cell'>";
        if ($fila['estado'] === 'Recibido') {
            $contenidoPrincipal .= "
                <form action='gestion_pedidos.php' method='POST' class='form-inline'>
                    <input type='hidden' name='id_pedido' value='{$fila['id']}'>
                    <input type='hidden' name='accion' value='cancelar'>
                    <button type='submit' class='btn-cancelar-pedido-admin'>Cancelar</button>
                </form>
            ";
        } else {
            $contenidoPrincipal .= "<span class='gestion-pedidos-sin-acciones'>Sin acciones</span>";
        }
        $contenidoPrincipal .= "</td>";
        $contenidoPrincipal .= "</tr>";
    }
    
    $contenidoPrincipal .= "</tbody></table>";
} else {
    $contenidoPrincipal .= "<div class='gestion-pedidos-empty'>
        <h3 class='gestion-pedidos-empty-title'>No hay pedidos registrados en el sistema todavia.</h3>
    </div>";
}

require 'includes/vistas/plantillas/plantilla.php';
?>