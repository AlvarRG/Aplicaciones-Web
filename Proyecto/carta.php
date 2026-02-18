<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Carta';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/carta.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';