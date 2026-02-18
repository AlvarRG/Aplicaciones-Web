<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Gestión de Productos';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/admin_productos.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';