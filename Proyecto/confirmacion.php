<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

// 1. SEGURIDAD: Usuario logueado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

// 2. RECUPERAR EL ID DEL PEDIDO
$idPedido = isset($_GET['pedido']) ? (int)$_GET['pedido'] : 0;
$idUsuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 1;

if ($idPedido === 0) {
    header('Location: index.php');
    exit();
}

// 3. CONSULTAR LOS DATOS DEL PEDIDO (Para mostrar el número diario y el estado)
// Aseguramos que el pedido pertenece a este usuario por privacidad
$queryPedidoConfirmacion = "SELECT numero_pedido, estado FROM Pedidos WHERE id = ? AND id_usuario = ?";
$rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidoConfirmacion, "ii", $idPedido, (int)$idUsuario)->get_result();

if ($rs && $rs->num_rows > 0) {
    $fila = $rs->fetch_assoc();
    $numeroPedido = $fila['numero_pedido'];
    $estado = $fila['estado'];
} else {
    // Si intentan poner un ID que no existe o no es suyo
    header('Location: index.php');
    exit();
}
if ($rs) {
    $rs->free();
}

$estilosExtra = ['confirmacion.css'];

$tituloPagina = 'Pedido Confirmado';

$contenidoPrincipal = <<<EOS
    <div class="confirmacion-wrapper">
        
        <h1 class="confirmacion-title">Pedido Confirmado</h1>
        <p class="confirmacion-subtitle">Tu pedido ha sido registrado correctamente en nuestro sistema.</p>
        
        <div class="confirmacion-box">
            <p>Tu número de pedido es:</p>
            <div class="confirmacion-numero">
                #{$numeroPedido}
            </div>
            
            <p class="confirmacion-estado">
                Estado actual: 
                <strong class="confirmacion-estado-badge">
                    {$estado}
                </strong>
            </p>
        </div>

        <p class="confirmacion-texto">
            Puedes consultar el estado de tu pedido en cualquier momento desde tu perfil de usuario.
        </p>

        <a href="carta.php" class="confirmacion-boton">
            Volver al Inicio (Nueva Compra)
        </a>
        
    </div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';
