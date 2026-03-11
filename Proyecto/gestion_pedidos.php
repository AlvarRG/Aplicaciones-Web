<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Pedido;

// Redirigimos si el usuario no ha iniciado sesión
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

// Leemos los roles del usuario desde la sesión
$esAdmin    = isset($_SESSION['esAdmin'])    ? $_SESSION['esAdmin']    : false;
$esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;
$esCocinero = isset($_SESSION['esCocinero']) ? $_SESSION['esCocinero'] : false;

// Solo el personal del restaurante puede acceder a esta página
$esPersonal = $esAdmin || $esCamarero || $esCocinero;

if (!$esPersonal) {
    header('Location: index.php');
    exit();
}

// Determinamos el nombre del rol para mostrarlo en la vista (el más prioritario gana)
$nombreRol = "Personal";
if ($esCamarero) $nombreRol = "Camarero";
if ($esCocinero) $nombreRol = "Cocinero";
if ($esAdmin)    $nombreRol = "Gerente";

// Procesamos la acción de cancelar pedido si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int)$_POST['id_pedido'];

    // Solo se puede cancelar un pedido si está en estado 'Recibido'
    Pedido::cambiarEstado($idPed, 'Cancelado');

    // Redirigimos para evitar reenvío del formulario al refrescar
    header('Location: gestion_pedidos.php');
    exit();
}

// Parámetros para la plantilla
$estilosExtra = ['gestion_pedidos.css'];

$tituloPagina = 'Gestion Global de Pedidos';

// Obtenemos todos los pedidos junto con el nombre del cliente, ordenados por fecha
$listaPedidos = Pedido::todosConCliente();

// Cabecera de la página con el rol del usuario actual
$contenidoPrincipal = <<<EOS
    <div class="gestion-header">
        <h1 class="gestion-header-title">Panel de Gestion de Pedidos</h1>
        <span class="gestion-header-rol">Vista: {$nombreRol}</span>
    </div>
EOS;

// Construimos las filas de la tabla (solo la parte variable)
if (!empty($listaPedidos)) {
    $filasTabla = "";
    foreach ($listaPedidos as $fila) {
        $totalFmt = number_format($fila['total'], 2, '.', '');

        // Clase CSS del badge según el estado del pedido
        $claseEstado = 'badge-estado--generico';
        switch ($fila['estado']) {
            case 'Recibido':        $claseEstado = 'badge-estado--recibido';     break;
            case 'En preparacion':
            case 'Cocinando':       $claseEstado = 'badge-estado--preparacion';  break;
            case 'Listo cocina':    $claseEstado = 'badge-estado--listo-cocina'; break;
            case 'Terminado':
            case 'Entregado':       $claseEstado = 'badge-estado--terminado';    break;
            case 'Cancelado':       $claseEstado = 'badge-estado--cancelado';    break;
        }

        $badgeEstado = "<span class='badge-estado {$claseEstado}'>{$fila['estado']}</span>";

        // Columna de acciones: solo los pedidos 'Recibido' se pueden cancelar
        if ($fila['estado'] === 'Recibido') {
            $accion = "
                <form action='gestion_pedidos.php' method='POST' class='form-inline'>
                    <input type='hidden' name='id_pedido' value='{$fila['id']}'>
                    <input type='hidden' name='accion' value='cancelar'>
                    <button type='submit' class='btn-cancelar-pedido-admin'>Cancelar</button>
                </form>";
        } else {
            $accion = "<span class='gestion-pedidos-sin-acciones'>Sin acciones</span>";
        }

        $filasTabla .= <<<EOS
            <tr class='gestion-pedidos-row'>
                <td class='gestion-pedidos-cell gestion-pedidos-cell--numero'>#{$fila['numero_pedido']}</td>
                <td class='gestion-pedidos-cell'>{$fila['fecha']}</td>
                <td class='gestion-pedidos-cell'>{$fila['nombre_cliente']}</td>
                <td class='gestion-pedidos-cell'>{$fila['tipo']}</td>
                <td class='gestion-pedidos-cell gestion-pedidos-total'><strong>{$totalFmt} euros</strong></td>
                <td class='gestion-pedidos-cell'>{$badgeEstado}</td>
                <td class='gestion-pedidos-cell'>{$accion}</td>
            </tr>
        EOS;
    }

    // Tabla completa con las filas construidas
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
    // Mensaje si no hay ningún pedido en el sistema
    $contenidoPrincipal .= <<<EOS
        <div class='gestion-pedidos-empty'>
            <h3 class='gestion-pedidos-empty-title'>No hay pedidos registrados en el sistema todavia.</h3>
        </div>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';