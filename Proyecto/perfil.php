<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioPerfil;
use es\ucm\fdi\aw\Aplicacion;

// 1. Verificación de seguridad
if (!isset($_SESSION['login']) || !isset($_SESSION['nombreUsuario'])) {
    header('Location: login.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();
$u = $conn->real_escape_string($_SESSION['nombreUsuario']);

// 2. Recuperar datos
$query = "SELECT avatar, nombreUsuario FROM usuarios WHERE nombreUsuario = '$u'";
$rs = $conn->query($query);
$datos = $rs->fetch_assoc();

if (!$datos) {
    die("Error crítico: No se han encontrado datos para el usuario logueado ($u).");
}

$tituloPagina = 'Mi Perfil';

// 3. Instanciamos la clase pasándole el nombre de usuario
$form = new FormularioPerfil($u);
$htmlFormulario = $form->gestiona();

// 4. Montamos la vista manteniendo el layout Flexbox original
$contenidoPrincipal = <<<EOS
    <h1>Perfil de {$datos['nombreUsuario']}</h1>
    
    <div style="display:flex; gap: 30px; align-items: flex-start; margin-top: 20px;">
        
        <div>
            <img src="img/avatares/{$datos['avatar']}" style="width: 150px; height: 150px; border-radius: 10px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        </div>
        
        <div style="flex-grow: 1; max-width: 500px;">
            $htmlFormulario
        </div>
        
    </div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';