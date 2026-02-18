<?php
// Archivo: C:\xampp\htdocs\practica2\procesar_login.php
require_once __DIR__ . '/includes/config.php';

// Comprobamos si nos llegan datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // 1. Caso de Error simulado
    if ($user === 'error' && $pass === 'error') {
        $_SESSION['error_login'] = "Usuario o contraseña incorrectos. Inténtalo de nuevo.";
        header('Location: login.php'); // Volvemos al formulario
        exit();
    }

    // 2. Si no hay error, "iniciamos sesión"
    $_SESSION['login'] = true;
    $_SESSION['nombre_usuario'] = $user;

    // Asignamos roles según lo que has pedido
    if ($user === 'admin' && $pass === 'admin') {
        $_SESSION['rol'] = 'gerente';
    } elseif ($user === 'cocinero' && $pass === 'cocinero') {
        $_SESSION['rol'] = 'cocinero';
    } elseif ($user === 'camarero' && $pass === 'camarero') {
        $_SESSION['rol'] = 'camarero';
    } else {
        // Cualquier otra cosa es un cliente normal
        $_SESSION['rol'] = 'cliente';
    }

    // Redirigimos a la página principal tras loguearse
    header('Location: index.php');
    exit();
} else {
    // Si alguien entra aquí sin enviar formulario, lo echamos al login
    header('Location: login.php');
    exit();
}