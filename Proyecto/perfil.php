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

$idUsuario = $datos['id'];

$estadosSeguimiento = "'En preparacion', 'Cocinando', 'Listo cocina', 'Terminado'";
$queryActivos = "SELECT * FROM pedidos WHERE id_usuario = $idUsuario AND estado IN ($estadosSeguimiento) ORDER BY fecha DESC";
$rsActivos = $conn->query($queryActivos);

$htmlActivos = "";
if ($rsActivos && $rsActivos->num_rows > 0) {
    while ($ped = $rsActivos->fetch_assoc()) {
        $htmlActivos .= "
        <div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
            <div style='display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;'>
                <strong style='font-size: 1.1em;'>Pedido #{$ped['numero_pedido']}</strong>
                <span style='background: #e0f7fa; color: #006064; padding: 4px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold;'>{$ped['estado']}</span>
            </div>
            <div style='font-size: 0.95em; color: #555;'>
                <p style='margin: 5px 0;'><strong>Fecha:</strong> {$ped['fecha']}</p>
                <p style='margin: 5px 0;'><strong>Tipo:</strong> {$ped['tipo']}</p>
                <p style='margin: 10px 0 0 0; text-align: right; font-size: 1.2em; color: #333;'><strong>Total: " . number_format($ped['total'], 2) . "€</strong></p>
            </div>
        </div>";
    }
} else {
    $htmlActivos = "<div style='padding: 20px; background: white; border-radius: 8px; border: 1px dashed #ccc; text-align: center; color: #777;'>No tienes pedidos en curso actualmente.</div>";
}

$queryHistorial = "SELECT * FROM pedidos WHERE id_usuario = $idUsuario ORDER BY fecha DESC";
$rsHistorial = $conn->query($queryHistorial);

$htmlHistorial = "<table style='width: 100%; border-collapse: collapse; margin-top: 15px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
    <thead style='background-color: #333; color: white;'>
        <tr>
            <th style='padding: 12px; text-align: left;'>Nº Pedido</th>
            <th style='padding: 12px; text-align: left;'>Fecha</th>
            <th style='padding: 12px; text-align: left;'>Tipo</th>
            <th style='padding: 12px; text-align: left;'>Estado</th>
            <th style='padding: 12px; text-align: right;'>Total</th>
        </tr>
    </thead>
    <tbody>";

if ($rsHistorial && $rsHistorial->num_rows > 0) {
    while ($ped = $rsHistorial->fetch_assoc()) {
        $htmlHistorial .= "
        <tr style='border-bottom: 1px solid #eee;'>
            <td style='padding: 12px;'>#{$ped['numero_pedido']}</td>
            <td style='padding: 12px;'>{$ped['fecha']}</td>
            <td style='padding: 12px;'>{$ped['tipo']}</td>
            <td style='padding: 12px;'>{$ped['estado']}</td>
            <td style='padding: 12px; text-align: right; font-weight: bold;'>".number_format($ped['total'], 2)."€</td>
        </tr>";
    }
} else {
    $htmlHistorial .= "<tr><td colspan='5' style='padding: 20px; text-align: center; color: #777;'>No hay historial de pedidos.</td></tr>";
}
$htmlHistorial .= "</tbody></table>";

$avatares = ['alvar.jpg', 'ethan.jpg', 'yago.jpg', 'zhirun.jpg'];
$htmlAvatares = "";
foreach($avatares as $av) {
    $checked = ($datos['avatar'] == $av) ? "checked" : "";
    $htmlAvatares .= "<label style='margin-right: 10px; cursor: pointer;'><img src='img/avatares/$av' width='40' style='vertical-align: middle; border-radius: 5px; margin-right: 5px;'><input type='radio' name='avatar_pre' value='$av' $checked></label>";
}

$contenidoPrincipal = <<<EOS
    <div style="background: #333; color: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; font-size: 1.8em;">Perfil de {$datos['nombreUsuario']}</h1>
        <img src="img/avatares/{$datos['avatar']}" width="60" height="60" style="border-radius: 50%; border: 2px solid white; object-fit: cover;">
    </div>

    <div style="display:flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <form action="procesarPerfil.php" method="POST" enctype="multipart/form-data">
                <fieldset style="border: none; padding: 0;">
                    <legend style="font-size: 1.3em; font-weight: bold; margin-bottom: 20px; color: #333;">Actualizar mis datos</legend>
                    <p>Nombre:<br><input type="text" name="nombre" value="{$datos['nombre']}" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;"></p>
                    <p>Apellidos:<br><input type="text" name="apellidos" value="{$datos['apellidos']}" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;"></p>
                    <p>Email:<br><input type="email" name="email" value="{$datos['email']}" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;"></p>
                    
                    <h4 style="margin: 25px 0 10px 0;">Cambiar Avatar</h4>
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px;">$htmlAvatares</div>
                    <p>O sube uno propio:<br><input type="file" name="nueva_foto" style="margin-top: 5px;"></p>
                    <p style="margin-top: 15px;"><input type="checkbox" name="borrar_foto"> Usar foto por defecto</p>
                    
                    <button type="submit" name="actualizar" style="background: #333; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 20px; width: 100%;">Guardar Cambios</button>
                </fieldset>
            </form>
        </div>

        <div style="flex: 2; min-width: 400px; display: flex; flex-direction: column; gap: 30px;">
            <div style="background: #eceff1; padding: 25px; border-radius: 12px;">
                <h2 style="margin-top: 0; color: #455a64; border-bottom: 2px solid #cfd8dc; padding-bottom: 10px; margin-bottom: 20px;">Estado de mis pedidos</h2>
                <div>$htmlActivos</div>
            </div>

            <div style="background: #eceff1; padding: 25px; border-radius: 12px;">
                <h2 style="margin-top: 0; color: #455a64; border-bottom: 2px solid #cfd8dc; padding-bottom: 10px; margin-bottom: 20px;">Historial de pedidos</h2>
                <div style="overflow-x: auto;">$htmlHistorial</div>
            </div>
        </div>
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';