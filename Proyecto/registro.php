<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioRegistro;

$tituloPagina = 'Registro';

// Instanciar la clase del formulario
$form = new FormularioRegistro();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
    <h1>Registro de usuario</h1>
    $htmlFormulario
EOS;

require 'includes/vistas/plantillas/plantilla.php';