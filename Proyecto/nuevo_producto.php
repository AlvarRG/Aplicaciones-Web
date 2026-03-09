<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioNuevoProducto;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$tituloPagina = 'Nuevo Producto';

$form = new FormularioNuevoProducto();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
    <h1>Añadir Producto a la Carta</h1>
    <p><a href="admin_productos.php">⬅ Volver al listado</a></p>
    $htmlFormulario
    <script src="js/productos.js"></script>
EOS;

require 'includes/vistas/plantillas/plantilla.php';