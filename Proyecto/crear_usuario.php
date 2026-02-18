<?php
// Archivo: C:\xampp\htdocs\practica2\crear_usuario.php

require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Añadir Nuevo Empleado';

ob_start();
require __DIR__ . '/includes/vistas/plantillas/formulario_usuario.php';
$contenidoPrincipal = ob_get_clean();

require __DIR__ . '/includes/vistas/plantilla.php';