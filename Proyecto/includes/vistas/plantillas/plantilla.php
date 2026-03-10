<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<title><?= $tituloPagina //El título de la página, que cada una rellena?></title>
		<link rel="stylesheet" type="text/css" href="css/estilo.css?v=1.1">
		<?php //Si la página tiene estilos extra se los pone
            if (isset($estilosExtra)) {
                foreach ($estilosExtra as $estilo) {
                    echo '<link rel="stylesheet" type="text/css" href="css/' . htmlspecialchars($estilo, ENT_QUOTES, 'UTF-8') . '" />';
                }
            }
		?>
	</head>
	<body>
		<div id="contenedor">
		<?php //Cabecera y barra izquierda
			require('includes/vistas/comun/cabecera.php');
			require('includes/vistas/comun/sideBarIzq.php');
		?>
			<main>
				<article>
					<?= $contenidoPrincipal //El contenido principal de la página, que cada una rellena?>
				</article>
			</main>
		<?php //Barra derecha y pie de página
			require('includes/vistas/comun/sidebarDer.php');
			require('includes/vistas/comun/pie.php');
		?>
		</div>
        <?php  //Si la página tiene scripts extra los incluye
            if (isset($scriptsExtra)) {
                foreach ($scriptsExtra as $script) {
                    echo '<script src="js/' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>';
                }
            }
        ?>
	</body>
</html>