<?php
require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\pedidos\Pedido;

use es\ucm\fdi\aw\usuarios\FormularioPerfil;

if (!isset($_SESSION['login']) || !isset($_SESSION['nombreUsuario'])) {
    header('Location: login.php');
    exit();
}

$estilosExtra = ['perfil.css'];

//Recuperamos la información del usuario logueado desde la capa de dominio
$nombreUsuario = (string) $_SESSION['nombreUsuario'];
$usuarioObj = Usuario::buscaUsuario($nombreUsuario);
if (!$usuarioObj) {
    header('Location: logout.php');
    exit();
}

//Para no tocar demasiado HTML existente, montamos un array compatible
$usuario = [
    'id' => $usuarioObj->getId(),
    'nombreUsuario' => $usuarioObj->getNombreUsuario(),
    'nombre' => $usuarioObj->getNombre(),
    'avatar' => $usuarioObj->getAvatar(),
];

//Obtención de pedidos activos del usuario (Pedidos en curso)
$pedidosActivos = Pedido::activosPorUsuario((int) $usuario['id']);

//Pestañas de pedidos activos
$htmlActivos = "";
if (!empty($pedidosActivos)) {
    foreach ($pedidosActivos as $pedido) {
    $htmlActivos .= "
		<article class='perfil-pedido-activo'>
			<header class='perfil-pedido-activo-header'>
				<h3 class='perfil-pedido-activo-titulo'>Pedido #{$pedido->getNumeroPedido()}</h3>
				<span class='perfil-pedido-activo-estado'>{$pedido->getEstado()}</span>
			</header>
			<dl class='perfil-pedido-activo-detalle'>
				<div><dt>Fecha:</dt> <dd>{$pedido->getFecha()}</dd></div>
				<div><dt>Tipo:</dt> <dd>{$pedido->getTipo()}</dd></div>
				<div class='perfil-pedido-activo-total'><dt>Total:</dt> <dd>" . number_format($pedido->getTotal(), 2) . "€</dd></div>
			</dl>
		</article>";
	}
} else {
    $htmlActivos = "<div class='perfil-pedido-activo-vacio'>No tienes pedidos en curso actualmente.</div>";
}

//Obtención de pedidos entregados o cancelados del usuario (Historial de pedidos)
$pedidosHistorial = Pedido::historialPorUsuario((int) $usuario['id']);

//Contenido tabla de historial de pedidos
if (!empty($pedidosHistorial)) {
    $filasHistorial = "";
    foreach ($pedidosHistorial as $ped) {
        $total = number_format($ped->getTotal(), 2);
        $filasHistorial .= "
        <tr class='perfil-historial-row'>
            <td class='perfil-historial-cell'>#{$ped->getNumeroPedido()}</td>
            <td class='perfil-historial-cell'>{$ped->getFecha()}</td>
            <td class='perfil-historial-cell'>{$ped->getTipo()}</td>
            <td class='perfil-historial-cell'>{$ped->getEstado()}</td>
            <td class='perfil-historial-cell perfil-historial-cell--total'>{$total}€</td>
        </tr>";
    }
} else {
    $filasHistorial = "<tr><td colspan='5' class='perfil-historial-vacio'>No hay historial de pedidos.</td></tr>";
}

//Tabla de historial de pedidos una vez obtenido el contenido
$htmlHistorial = <<<EOS
    <table class='perfil-historial-tabla'>
        <thead class='perfil-historial-thead'>
            <tr>
                <th class='perfil-historial-th'>Nº Pedido</th>
                <th class='perfil-historial-th'>Fecha</th>
                <th class='perfil-historial-th'>Tipo</th>
                <th class='perfil-historial-th'>Estado</th>
                <th class='perfil-historial-th'>Total</th>
            </tr>
        </thead>
        <tbody>$filasHistorial</tbody>
    </table>
EOS;

//Formulario de edición de perfil
$formPerfil = new FormularioPerfil($_SESSION['nombreUsuario']);
$htmlFormPerfil = $formPerfil->gestiona();

//Parámetros para la plantilla
$tituloPagina = 'Mi Perfil';

$rutaImgs = RUTA_IMGS;

$contenidoPrincipal = <<<EOS
    <header class="perfil-header">
        <h1 class="perfil-header-title">Perfil de {$usuario['nombreUsuario']}</h1>
        <img src="{$rutaImgs}/avatares/{$usuario['avatar']}" 
             class="perfil-header-avatar" 
             alt="Avatar de {$usuario['nombreUsuario']}">
    </header>

    <div class="perfil-layout">
        <section class="perfil-form-wrapper">
            <h2 class="perfil-panel-title">Datos Personales</h2>
            $htmlFormPerfil
        </section>

        <div class="perfil-panels">
            <section class="perfil-panel">
                <h2 class="perfil-panel-title">Estado de mis pedidos</h2>
                $htmlActivos
            </section>

            <section class="perfil-panel">
                <h2 class="perfil-panel-title">Historial de pedidos</h2>
                <div class="perfil-historial-wrapper">$htmlHistorial</div>
            </section>
        </div>
    </div>
EOS;

require __DIR__ . '/includes/vistas/plantillas/plantilla.php';