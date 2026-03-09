<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

// 1. SEGURIDAD: Usuario logueado obligatoriamente
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();

// 2. RECUPERAR EL ID DEL USUARIO (A prueba de fallos)
$idUsuario = 0;

if (isset($_SESSION['id_usuario'])) {
    $idUsuario = (int)$_SESSION['id_usuario'];
} elseif (isset($_SESSION['id'])) {
    $idUsuario = (int)$_SESSION['id'];
}

// PARCHE AUTOSANADOR: Si no hay ID pero tenemos el nombre, lo buscamos en la base de datos
if ($idUsuario === 0 && isset($_SESSION['nombreUsuario'])) {
    $nombreUser = $conn->real_escape_string($_SESSION['nombreUsuario']);
    $rsBusqueda = $conn->query("SELECT id FROM usuarios WHERE nombreUsuario = '$nombreUser'");
    if ($rsBusqueda && $rsBusqueda->num_rows > 0) {
        $idUsuario = (int)$rsBusqueda->fetch_assoc()['id'];
        $_SESSION['id'] = $idUsuario; // Lo guardamos para que no vuelva a fallar
    }
}

// SISTEMA DE DEBUGGING: Si llegamos aquí y sigue siendo 0, detenemos TODO antes del error fatal.
if ($idUsuario === 0) {
    echo "<div class='debug-session-error'>";
    echo "<h3>⚠️ Alto ahí: Falta el ID del usuario</h3>";
    echo "<p>El sistema no sabe quién eres porque tu archivo de Login no guardó tu ID al iniciar sesión.</p>";
    echo "<p>Esto es lo que el servidor XAMPP conoce de ti ahora mismo en la variable \$_SESSION:</p>";
    echo "<pre class='debug-session-dump'>";
    var_dump($_SESSION);
    echo "</pre>";
    echo "<p><strong>Copia lo que sale en la caja blanca de arriba y pásamelo</strong> para que te diga qué tienes que cambiar en tu Login.</p>";
    echo "</div>";
    exit(); // Esto aborta la página para que NO llegue a la línea del error SQL.
}

// 3. PROCESAR CANCELACIÓN DEL CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $idPed = (int)$_POST['id_pedido'];
    
    // Verificamos que el pedido sea SUYO y esté en estado 'Recibido'
    $queryCheck = "SELECT estado FROM Pedidos WHERE id = $idPed AND id_usuario = $idUsuario";
    $rsCheck = $conn->query($queryCheck);
    
    if ($rsCheck && $rsCheck->num_rows > 0) {
        $estadoActual = $rsCheck->fetch_assoc()['estado'];
        if ($estadoActual === 'Recibido') {
            $queryCancel = "UPDATE Pedidos SET estado = 'Cancelado' WHERE id = $idPed";
            $conn->query($queryCancel);
        }
    }
    
    header('Location: mis_pedidos.php');
    exit();
}

$tituloPagina = 'Mis Pedidos';
$estilosExtra = $estilosExtra ?? [];
$scriptsExtra = $scriptsExtra ?? [];
$scriptsExtra[] = 'confirmacion_cancelar_pedido.js';

$query = "SELECT * FROM Pedidos WHERE id_usuario = $idUsuario ORDER BY fecha DESC";
$rs = $conn->query($query);

$contenidoPrincipal = <<<EOS
    <h1>Historial de Mis Pedidos</h1>
    <p>Aquí puedes consultar el estado de tus pedidos y tu historial de compras.</p>
EOS;

if ($rs && $rs->num_rows > 0) {
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
    
    while ($fila = $rs->fetch_assoc()) {
        $totalFmt = number_format($fila['total'], 2, '.', '');
        
        $claseEstado = 'badge-estado--generico'; 
        switch ($fila['estado']) {
            case 'Recibido': $claseEstado = 'badge-estado--recibido'; break; 
            case 'En preparación': 
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
        // Acción: Cancelar (Solo si está en estado 'Recibido')
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

require 'includes/vistas/plantillas/plantilla.php';
?>