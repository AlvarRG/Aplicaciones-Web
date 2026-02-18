<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Pasarela de Pago';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/pago.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';