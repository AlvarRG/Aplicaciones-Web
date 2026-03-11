<?php
require_once __DIR__.'/includes/config.php';

//Para cerrar sesión, actualizamos _SESSION y destruímos la sesión
unset($_SESSION['login']);
unset($_SESSION['esAdmin']);
unset($_SESSION['nombre']);

session_destroy();

$tituloPagina = 'Logout';

$contenidoPrincipal = <<<EOS
    <h1>Hasta pronto!</h1>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';