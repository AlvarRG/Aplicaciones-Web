<?php
require_once __DIR__ . '/includes/config.php';
$tituloPagina = 'Administración de Usuarios';
ob_start();
require __DIR__ . '/includes/vistas/plantillas/admin_usuarios.php';
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/vistas/plantilla.php';