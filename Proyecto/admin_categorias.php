<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Categorías del Menú';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/admin_categorias.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';