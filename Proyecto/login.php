<?php
// Archivo: C:\xampp\htdocs\practica2\login.php

require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Iniciar Sesión';

// Cargamos la vista del login (que ya creamos antes y que contiene el HTML y el mensaje de error)
ob_start();
require __DIR__ . '/includes/vistas/plantillas/login.php';
$contenidoPrincipal = ob_get_clean();

// Cargamos la plantilla base
require __DIR__ . '/includes/vistas/plantilla.php';