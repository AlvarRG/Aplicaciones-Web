<?php
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Pedido Confirmado';

ob_start();
require __DIR__ . '/includes/vistas/plantillas/pedido_confirmado.php';
$contenidoPrincipal = ob_get_clean();

require __DIR__ . '/includes/vistas/plantilla.php';