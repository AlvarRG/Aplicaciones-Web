<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;

// Recibe el parámetro user mediante GET
$user = $_GET['user'] ?? '';

// Lógica de comprobación
if (Usuario::buscaUsuario($user)) {
    echo "existe";
} else {
    echo "disponible";
}