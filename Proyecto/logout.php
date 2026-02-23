<?php
//Inicio del procesamiento
session_start();

//Doble seguridad: unset + destroy
unset($_SESSION['login']);
unset($_SESSION['esAdmin']);
unset($_SESSION['nombre']);

$tituloPagina = 'Logout';
$contenidoPrincipal = <<<EOS
<h1>Hasta pronto!</h1>
EOS;


session_destroy();
require 'includes/vistas/plantillas/plantilla.php';
?>