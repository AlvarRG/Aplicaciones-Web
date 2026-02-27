<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioNuevaCategoria;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    exit("No tienes permisos");
}

$form = new FormularioNuevaCategoria();
$htmlFormulario = $form->gestiona();

$tituloPagina = 'Nueva Categoría';

$contenidoPrincipal = <<<EOS
    <h1>Crear Categoría</h1>
    <p><a href="admin_categorias.php">⬅ Volver al listado</a></p>
    $htmlFormulario
EOS;

require 'includes/vistas/plantillas/plantilla.php';