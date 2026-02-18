<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Añadir Categoría';
ob_start();
// Cargamos la plantilla del formulario
require __DIR__ . '/includes/vistas/plantillas/formulario_categoria.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';