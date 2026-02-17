<?php
// C:\xampp\htdocs\practica2\perfil.php

require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Mi Perfil';

// 1. Aquí "cargamos" la comida (el HTML de la plantilla)
ob_start();
require __DIR__ . '/includes/vistas/plantillas/perfil.php'; 
$contenidoPrincipal = ob_get_clean();

// 2. Aquí servimos la mesa (ponemos cabecera, contenido y pie)
require __DIR__ . '/includes/vistas/plantilla.php';