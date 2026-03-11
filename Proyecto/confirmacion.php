<?php
require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\Pedido;

//Si el usuario no está logeado le mandamos al login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

//Cogemos el id del pedido y el id del usuario
$idPedido = isset($_GET['pedido']) ? (int)$_GET['pedido'] : 0;
$idUsuario = (int)$_SESSION['id'];

if ($idPedido === 0) {
    header('Location: index.php');
    exit();
}

//Cogemos los datos del pedido
$pedido = Pedido::porIdYUsuario($idPedido, (int)$idUsuario);

if ($pedido) {
    $numeroPedido = $pedido['numero_pedido'];
    $estado = $pedido['estado'];
}
else {
    //Si intentan poner un ID que no existe o no es suyo
    header('Location: index.php');
    exit();
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

require __DIR__ . '/includes/vistas/plantillas/plantilla.php';
