<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Añadir Nuevo Producto';
ob_start();
// Reutilizamos el formulario que hemos creado
require __DIR__ . '/includes/vistas/plantillas/formulario_producto.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';