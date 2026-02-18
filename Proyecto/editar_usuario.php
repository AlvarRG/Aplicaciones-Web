<?php
// Archivo: C:\xampp\htdocs\practica2\editar_usuario.php

require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Editar Usuario / Rol';

ob_start();
// Reutilizamos exactamente la misma plantilla
require __DIR__ . '/includes/vistas/plantillas/formulario_usuario.php';
$contenidoPrincipal = ob_get_clean();

require __DIR__ . '/includes/vistas/plantilla.php';