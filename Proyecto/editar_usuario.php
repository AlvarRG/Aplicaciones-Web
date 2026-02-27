<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioEditarUsuario;
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
     die("Acceso denegado.");
}

$conn = Aplicacion::getInstance()->getConexionBd();
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$res = $conn->query("SELECT nombreUsuario FROM usuarios WHERE id = $id");
$usuario = $res->fetch_assoc();

$tituloPagina = 'Editar Rol';

$form = new FormularioEditarUsuario($id);
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
    <h1>Editar Rol de: {$usuario['nombreUsuario']}</h1>
    <p><a href="admin_usuarios.php">⬅ Volver al listado</a></p>
    $htmlFormulario
EOS;

require 'includes/vistas/plantillas/plantilla.php';