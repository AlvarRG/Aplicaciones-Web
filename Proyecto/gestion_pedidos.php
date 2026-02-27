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

$query = "SELECT p.id, p.numero_pedido, p.fecha, p.estado, p.tipo, p.total, u.nombre AS nombre_cliente 
          FROM Pedidos p 
          JOIN usuarios u ON p.id_usuario = u.id 
          ORDER BY p.fecha DESC";
$rs = $conn->query($query);

$contenidoPrincipal = <<<EOS
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #303030; padding-bottom: 10px; margin-bottom: 20px;">
        <h1 style="margin: 0;">Panel de Gestion de Pedidos</h1>
        <span style="background: #303030; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold;">Vista: {$nombreRol}</span>
    </div>
EOS;

if ($rs && $rs->num_rows > 0) {
    $contenidoPrincipal .= "<table style='width:100%; text-align:center; border-collapse:collapse; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
    $contenidoPrincipal .= "<thead style='background:#303030; color:black;'>
        <tr>
            <th style='padding: 15px; border: 1px solid #555;'>N Pedido</th>
            <th style='border: 1px solid #555;'>Fecha y Hora</th>
            <th style='border: 1px solid #555;'>Cliente</th>
            <th style='border: 1px solid #555;'>Tipo</th>
            <th style='border: 1px solid #555;'>Total</th>
            <th style='border: 1px solid #555;'>Estado</th>
            <th style='border: 1px solid #555;'>Acciones</th>
        </tr>
    </thead><tbody>";
    
    while ($fila = $rs->fetch_assoc()) {
        $totalFmt = number_format($fila['total'], 2, '.', '');
        
        $colorEstado = '#6c757d'; 
        switch ($fila['estado']) {
            case 'Recibido': $colorEstado = '#ffc107'; break; 
            case 'En preparacion': 
            case 'Cocinando': $colorEstado = '#17a2b8'; break; 
            case 'Listo cocina': $colorEstado = '#fd7e14'; break; 
            case 'Terminado': 
            case 'Entregado': $colorEstado = '#28a745'; break; 
            case 'Cancelado': $colorEstado = '#dc3545'; break; 
        }
        
        $badgeEstado = "<span style='background: {$colorEstado}; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.9em; font-weight: bold;'>{$fila['estado']}</span>";
        
        $contenidoPrincipal .= "<tr style='border-bottom: 1px solid #eee;'>";
        $contenidoPrincipal .= "<td style='padding: 15px; font-weight: bold; font-size: 1.2em; border: 1px solid #ddd;'>#{$fila['numero_pedido']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$fila['fecha']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$fila['nombre_cliente']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$fila['tipo']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'><strong>{$totalFmt} euros</strong></td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$badgeEstado}</td>";
        
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>";
        if ($fila['estado'] === 'Recibido') {
            $contenidoPrincipal .= "
                <form action='gestion_pedidos.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='id_pedido' value='{$fila['id']}'>
                    <input type='hidden' name='accion' value='cancelar'>
                    <button type='submit' style='background:#dc3545; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold;'>Cancelar</button>
                </form>
            ";
        } else {
            $contenidoPrincipal .= "<span style='color: #aaa; font-style: italic;'>Sin acciones</span>";
        }
        $contenidoPrincipal .= "</td>";
        $contenidoPrincipal .= "</tr>";
    }
    
    $contenidoPrincipal .= "</tbody></table>";
} else {
    $contenidoPrincipal .= "<div style='background: #fff; padding: 30px; text-align: center; border: 1px solid #ddd; border-radius: 8px;'>
        <h3 style='color: #666;'>No hay pedidos registrados en el sistema todavia.</h3>
    </div>";
}

require 'includes/vistas/plantillas/plantilla.php';
?>