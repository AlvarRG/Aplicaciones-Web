<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<title><?= $tituloPagina ?></title>
		<link rel="stylesheet" type="text/css" href="css/estilo.css?v=1.1">
		<link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?>/estilo.css" />
		<?php 
			if (isset($estilosExtra)) {
				foreach ($estilosExtra as $estilo) {
					echo '<link rel="stylesheet" type="text/css" href="' . RUTA_CSS . '/' . $estilo . '" />';
				}
			}
		?>
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