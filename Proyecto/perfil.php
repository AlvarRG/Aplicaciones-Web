<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['login']) || !isset($_SESSION['nombreUsuario'])) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();
$u = $conn->real_escape_string($_SESSION['nombreUsuario']);

$query = "SELECT * FROM usuarios WHERE nombreUsuario = '$u'";
$rs = $conn->query($query);
$datos = $rs->fetch_assoc();

if (!$datos) {
    die("Error critico: No se han encontrado datos para el usuario logueado.");
}

$tituloPagina = 'Mi Perfil';
$estilosExtra = ['perfil.css'];

$idUsuario = $datos['id'];

$estadosSeguimiento = "'En preparacion', 'Cocinando', 'Listo cocina', 'Terminado'";
$queryActivos = "SELECT * FROM pedidos WHERE id_usuario = $idUsuario AND estado IN ($estadosSeguimiento) ORDER BY fecha DESC";
$rsActivos = $conn->query($queryActivos);

$htmlActivos = "";
if ($rsActivos && $rsActivos->num_rows > 0) {
    while ($ped = $rsActivos->fetch_assoc()) {
        $htmlActivos .= "
        <div class='perfil-pedido-activo'>
            <div class='perfil-pedido-activo-header'>
                <strong class='perfil-pedido-activo-titulo'>Pedido #{$ped['numero_pedido']}</strong>
                <span class='perfil-pedido-activo-estado'>{$ped['estado']}</span>
            </div>
            <div class='perfil-pedido-activo-detalle'>
                <p><strong>Fecha:</strong> {$ped['fecha']}</p>
                <p><strong>Tipo:</strong> {$ped['tipo']}</p>
                <p class='perfil-pedido-activo-total'><strong>Total: " . number_format($ped['total'], 2) . "€</strong></p>
            </div>
        </div>";
    }
} else {
    $htmlActivos = "<div class='perfil-pedido-activo-vacio'>No tienes pedidos en curso actualmente.</div>";
}

$queryHistorial = "SELECT * FROM pedidos WHERE id_usuario = $idUsuario ORDER BY fecha DESC";
$rsHistorial = $conn->query($queryHistorial);

$htmlHistorial = "<table class='perfil-historial-tabla'>
    <thead class='perfil-historial-thead'>
        <tr>
            <th class='perfil-historial-th'>Nº Pedido</th>
            <th class='perfil-historial-th'>Fecha</th>
            <th class='perfil-historial-th'>Tipo</th>
            <th class='perfil-historial-th'>Estado</th>
            <th class='perfil-historial-th'>Total</th>
        </tr>
    </thead>
    <tbody>";

if ($rsHistorial && $rsHistorial->num_rows > 0) {
    while ($ped = $rsHistorial->fetch_assoc()) {
        $htmlHistorial .= "
        <tr class='perfil-historial-row'>
            <td class='perfil-historial-cell'>#{$ped['numero_pedido']}</td>
            <td class='perfil-historial-cell'>{$ped['fecha']}</td>
            <td class='perfil-historial-cell'>{$ped['tipo']}</td>
            <td class='perfil-historial-cell'>{$ped['estado']}</td>
            <td class='perfil-historial-cell perfil-historial-cell--total'>".number_format($ped['total'], 2)."€</td>
        </tr>";
    }
} else {
    $htmlHistorial .= "<tr><td colspan='5' class='perfil-historial-vacio'>No hay historial de pedidos.</td></tr>";
}
$htmlHistorial .= "</tbody></table>";

$avatares = ['alvar.jpg', 'ethan.jpg', 'yago.jpg', 'zhirun.jpg'];
$htmlAvatares = "";
foreach($avatares as $av) {
    $checked = ($datos['avatar'] == $av) ? "checked" : "";
    $htmlAvatares .= "<label class='perfil-avatar-opcion'><img src='img/avatares/$av'><input type='radio' name='avatar_pre' value='$av' $checked></label>";
}

$contenidoPrincipal = <<<EOS
    <div class="perfil-header">
        <h1 class="perfil-header-title">Perfil de {$datos['nombreUsuario']}</h1>
        <img src="img/avatares/{$datos['avatar']}" class="perfil-header-avatar">
    </div>

    <div class="perfil-layout">
        <div class="perfil-form-wrapper">
            <form action="procesarPerfil.php" method="POST" enctype="multipart/form-data">
                <fieldset class="perfil-fieldset">
                    <legend class="perfil-legend">Actualizar mis datos</legend>
                    <p>Nombre:<br><input type="text" name="nombre" value="{$datos['nombre']}" class="perfil-input-text"></p>
                    <p>Apellidos:<br><input type="text" name="apellidos" value="{$datos['apellidos']}" class="perfil-input-text"></p>
                    <p>Email:<br><input type="email" name="email" value="{$datos['email']}" class="perfil-input-text"></p>
                    
                    <h4 class="perfil-avatar-title">Cambiar Avatar</h4>
                    <div class="perfil-avatar-box">$htmlAvatares</div>
                    <p>O sube uno propio:<br><input type="file" name="nueva_foto" class="perfil-file-input"></p>
                    <p class="perfil-checkbox"><input type="checkbox" name="borrar_foto"> Usar foto por defecto</p>
                    
                    <button type="submit" name="actualizar" class="perfil-submit">Guardar Cambios</button>
                </fieldset>
            </form>
        </div>

        <div class="perfil-panels">
            <div class="perfil-panel">
                <h2 class="perfil-panel-title">Estado de mis pedidos</h2>
                <div>$htmlActivos</div>
            </div>

            <div class="perfil-panel">
                <h2 class="perfil-panel-title">Historial de pedidos</h2>
                <div class="perfil-historial-wrapper">$htmlHistorial</div>
            </div>
        </div>
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';