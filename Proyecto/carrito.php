<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Carrito';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/carrito.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';