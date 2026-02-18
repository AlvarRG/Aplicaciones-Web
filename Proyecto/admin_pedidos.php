<?php
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Monitor de Pedidos';

// 1. Capturamos el HTML del monitor de pedidos
ob_start();
require __DIR__ . '/includes/vistas/plantillas/admin_pedidos.php';
$contenidoPrincipal = ob_get_clean();

// 2. Cargamos la plantilla maestra (cabecera, menú y pie)
require __DIR__ . '/includes/vistas/plantilla.php';