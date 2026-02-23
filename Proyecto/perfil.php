<?php
require_once __DIR__.'/utils.php';
session_start();

// 1. Verificación de seguridad
if (!isset($_SESSION['login']) || !isset($_SESSION['nombreUsuario'])) {
    header('Location: login.php');
    exit();
}

$conn = conexionBD();
$u = $conn->real_escape_string($_SESSION['nombreUsuario']);

// 2. Recuperar datos
$query = "SELECT * FROM Usuarios WHERE nombreUsuario = '$u'";
$rs = $conn->query($query);
$datos = $rs->fetch_assoc();

if (!$datos) {
    die("Error crítico: No se han encontrado datos para el usuario logueado ($u).");
}

$tituloPagina = 'Mi Perfil';

// 3. Preparar vista de avatares
$avatares = ['alvar.jpg', 'ethan.jpg', 'yago.jpg', 'zhirun.jpg'];
$htmlAvatares = "";
foreach($avatares as $av) {
    $checked = ($datos['avatar'] == $av) ? "checked" : "";
    $htmlAvatares .= "<label><img src='img/avatares/$av' width='40'><input type='radio' name='avatar_pre' value='$av' $checked></label>";
}

$contenidoPrincipal = <<<EOS
    <h1>Perfil de {$datos['nombreUsuario']}</h1>
    <div style="display:flex; gap: 20px;">
        <img src="img/avatares/{$datos['avatar']}" width="150" style="border-radius:10px;" height="120">
        
        <form action="procesarPerfil.php" method="POST" enctype="multipart/form-data">
            <fieldset>
                <legend>Actualizar mis datos</legend>
                <p>Nombre: <input type="text" name="nombre" value="{$datos['nombre']}"></p>
                <p>Apellidos: <input type="text" name="apellidos" value="{$datos['apellidos']}"></p>
                <p>Email: <input type="email" name="email" value="{$datos['email']}"></p>
                
                <h4>Cambiar Avatar</h4>
                <div>$htmlAvatares</div>
                <p>O sube uno propio: <input type="file" name="nueva_foto"></p>
                <p><input type="checkbox" name="borrar_foto"> Usar foto por defecto</p>
                
                <button type="submit" name="actualizar">Guardar Cambios</button>
            </fieldset>
        </form>
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';