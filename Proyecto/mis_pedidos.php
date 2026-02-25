<?php
require_once __DIR__.'/utils.php';
session_start();

// 1. SEGURIDAD: Usuario logueado obligatoriamente
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

$conn = conexionBD();

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
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; margin: 50px auto; max-width: 800px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
    echo "<h3>⚠️ Alto ahí: Falta el ID del usuario</h3>";
    echo "<p>El sistema no sabe quién eres porque tu archivo de Login no guardó tu ID al iniciar sesión.</p>";
    echo "<p>Esto es lo que el servidor XAMPP conoce de ti ahora mismo en la variable \$_SESSION:</p>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px; color: black;'>";
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

// 4. OBTENER SOLO LOS PEDIDOS DE ESTE USUARIO (Ahora sí, la consulta es 100% segura)
$query = "SELECT * FROM Pedidos WHERE id_usuario = $idUsuario ORDER BY fecha DESC";
$rs = $conn->query($query);

$contenidoPrincipal = <<<EOS
    <h1>📦 Historial de Mis Pedidos</h1>
    <p>Aquí puedes consultar el estado de tus pedidos y tu historial de compras.</p>
EOS;

if ($rs && $rs->num_rows > 0) {
    $contenidoPrincipal .= "<table style='width:100%; text-align:center; border-collapse:collapse; background: white; margin-top: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
    $contenidoPrincipal .= "<thead style='background:#303030; color:white;'>
        <tr>
            <th style='padding: 10px; border: 1px solid #555;'>Nº Pedido</th>
            <th style='border: 1px solid #555;'>Fecha</th>
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
            case 'En preparación': 
            case 'Cocinando': $colorEstado = '#17a2b8'; break; 
            case 'Listo cocina': $colorEstado = '#fd7e14'; break; 
            case 'Terminado': 
            case 'Entregado': $colorEstado = '#28a745'; break; 
            case 'Cancelado': $colorEstado = '#dc3545'; break; 
        }
        
        $badgeEstado = "<span style='background: {$colorEstado}; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.9em; font-weight: bold;'>{$fila['estado']}</span>";
        
        $contenidoPrincipal .= "<tr style='border-bottom: 1px solid #eee;'>";
        $contenidoPrincipal .= "<td style='padding: 15px; font-weight: bold; border: 1px solid #ddd;'>#{$fila['numero_pedido']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$fila['fecha']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$fila['tipo']}</td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'><strong>{$totalFmt} €</strong></td>";
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>{$badgeEstado}</td>";
        
        $contenidoPrincipal .= "<td style='border: 1px solid #ddd;'>";
        // Acción: Cancelar (Solo si está en estado 'Recibido')
        if ($fila['estado'] === 'Recibido') {
            $contenidoPrincipal .= "
                <form action='mis_pedidos.php' method='POST' style='display:inline;' onsubmit='return confirm(\"¿Seguro que deseas cancelar tu pedido?\");'>
                    <input type='hidden' name='id_pedido' value='{$fila['id']}'>
                    <input type='hidden' name='accion' value='cancelar'>
                    <button type='submit' style='background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;'>Cancelar</button>
                </form>
            ";
        } else {
            $contenidoPrincipal .= "<span style='color: #aaa; font-size: 0.9em;'>No cancelable</span>";
        }
        $contenidoPrincipal .= "</td>";
        $contenidoPrincipal .= "</tr>";
    }
    
    $contenidoPrincipal .= "</tbody></table>";
} else {
    $contenidoPrincipal .= "<div style='background: #f9f9f9; padding: 20px; text-align: center; border-radius: 8px; margin-top: 20px; border: 1px solid #ddd;'>
        <p>Aún no has realizado ningún pedido con nosotros.</p>
        <a href='carta.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 10px;'>🍽️ Ir a la Carta</a>
    </div>";
}

require 'includes/vistas/plantillas/plantilla.php';
?>