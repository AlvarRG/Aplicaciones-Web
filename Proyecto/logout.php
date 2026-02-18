<?php
// Archivo: C:\xampp\htdocs\practica2\logout.php
require_once __DIR__ . '/includes/config.php';

// Destruimos la sesión
session_destroy();

// Volvemos al inicio
header('Location: index.php');
exit();