<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;

// Recibe el parámetro email mediante GET
$email = $_GET['email'] ?? '';

// Lógica de comprobación
if (Usuario::existeEmail($email)) {
    echo "existe";
} else {
    echo "disponible";
}