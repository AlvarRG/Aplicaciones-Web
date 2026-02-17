<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Editar Perfil';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/editar_perfil.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';