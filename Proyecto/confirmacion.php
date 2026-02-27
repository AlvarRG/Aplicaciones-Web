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

$conn = Aplicacion::getInstance()->getConexionBd();

// 3. CONSULTAR LOS DATOS DEL PEDIDO (Para mostrar el número diario y el estado)
// Aseguramos que el pedido pertenece a este usuario por privacidad
$query = sprintf("SELECT numero_pedido, estado FROM Pedidos WHERE id = %d AND id_usuario = %d", 
    $idPedido, 
    $idUsuario
);
$rs = $conn->query($query);

if ($rs && $rs->num_rows > 0) {
    $fila = $rs->fetch_assoc();
    $numeroPedido = $fila['numero_pedido'];
    $estado = $fila['estado'];
} else {
    // Si intentan poner un ID que no existe o no es suyo
    header('Location: index.php');
    exit();
}

$tituloPagina = 'Pedido Confirmado';

// 4. VISTA DE CONFIRMACIÓN
$contenidoPrincipal = <<<EOS
    <div style="max-width: 600px; margin: 50px auto; text-align: center; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-top: 5px solid #28a745;">
        
        <h1 style="color: #28a745; font-size: 2.5em; margin-bottom: 10px;">¡Pedido Confirmado! 🎉</h1>
        <p style="font-size: 1.2em; color: #555;">Tu pedido ha sido registrado correctamente en nuestro sistema.</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 30px 0;">
            <p style="font-size: 1.1em; margin: 0; color: #666;">Tu número de pedido es:</p>
            <div style="font-size: 4em; font-weight: bold; color: #303030; line-height: 1;">
                #{$numeroPedido}
            </div>
            
            <p style="font-size: 1.2em; margin-top: 15px;">
                Estado actual: 
                <strong style="background: #e1f5fe; color: #0288d1; padding: 5px 15px; border-radius: 20px;">
                    {$estado}
                </strong>
            </p>
        </div>

        <p style="margin-bottom: 30px; color: #777;">
            Puedes consultar el estado de tu pedido en cualquier momento desde tu perfil de usuario.
        </p>

        <a href="carta.php" style="background-color: #303030; color: white; padding: 15px 30px; font-size: 1.2em; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
            Volver al Inicio (Nueva Compra)
        </a>
        
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';
?>