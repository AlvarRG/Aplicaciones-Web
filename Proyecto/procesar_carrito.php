<?php
require_once __DIR__.'/includes/config.php';

// Si alguien intenta entrar aquí sin enviar el formulario, lo echamos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carta.php');
    exit();
}

// 1. Inicializar el carrito por si acaso
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// 2. Recoger los datos enviados por el formulario
$id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
$accion = isset($_POST['accion']) ? $_POST['accion'] : 'add';

// 3. LÓGICA DEL CARRITO

// A) Acciones globales (No necesitan ID de producto)
if ($accion === 'vaciar') {
    // Vaciamos el array del carrito
    $_SESSION['carrito'] = []; 
} 
// B) Acciones sobre un producto específico (Sí necesitan ID)
elseif ($id_producto > 0) {
    if ($accion === 'add') {
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        }
    } 
    elseif ($accion === 'update') {
        if ($cantidad > 0) {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        } else {
            unset($_SESSION['carrito'][$id_producto]);
        }
    } 
    elseif ($accion === 'remove') {
        unset($_SESSION['carrito'][$id_producto]);
    }
}

// 4. Redirigir al usuario de vuelta
$urlDestino = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'carta.php';
header("Location: $urlDestino");
exit();