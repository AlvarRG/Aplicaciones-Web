<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<title><?= $tituloPagina ?></title>
		<link rel="stylesheet" type="text/css" href="css/estilo.css?v=1.1">
		<?php 
            if (isset($estilosExtra)) {
                foreach ($estilosExtra as $estilo) {
                    echo '<link rel="stylesheet" type="text/css" href="css/' . htmlspecialchars($estilo, ENT_QUOTES, 'UTF-8') . '" />';
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
        <?php 
            if (isset($scriptsExtra)) {
                foreach ($scriptsExtra as $script) {
                    echo '<script src="js/' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>';
                }
            }
        ?>
	</body>
</html>