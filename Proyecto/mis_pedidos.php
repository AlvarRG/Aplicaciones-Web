<?php
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Mis Pedidos';

ob_start();
require __DIR__ . '/includes/vistas/plantillas/mis_pedidos.php';
$contenidoPrincipal = ob_get_clean();

require __DIR__ . '/includes/vistas/plantilla.php';