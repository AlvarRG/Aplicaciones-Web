<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
	<link rel="stylesheet" type="text/css" href="css/estilo.css?v=1.1">
</head>
<body>
<div id="contenedor">
<?php
require('includes/vistas/comun/cabecera.php');
require('includes/vistas/comun/sideBarIzq.php');
?>
    <main>
        <article>
            <?= $contenidoPrincipal ?>
        </article>
    </main>
<?php
require('includes/vistas/comun/sidebarDer.php');
require('includes/vistas/comun/pie.php');
?>
</div>
</body>
</html>