<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Panel de Camarero';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/panel_camarero.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';