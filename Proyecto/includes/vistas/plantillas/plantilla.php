<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $tituloPagina //El título de la página, que cada una rellena?></title>
		<script>
			const RUTA_APP = "<?= RUTA_APP ?>";
		</script>
		<link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?>/estilo.css?v=1.1">
		<?php //Si la página tiene estilos extra se los pone
            if (isset($estilosExtra)) {
                foreach ($estilosExtra as $estilo) {
                    echo '<link rel="stylesheet" type="text/css" href="' . RUTA_CSS . '/' . htmlspecialchars($estilo, ENT_QUOTES, 'UTF-8') . '" />';
                }
            }
		?>
		<script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
		<script src="js/validaciones.js"></script>
		<script src="js/logica_carrito.js"></script>
	</head>
	<body>
		<div id="contenedor">
		<?php require('includes/vistas/comun/cabecera.php'); ?>

		<input type="checkbox" id="btn-menu-izq" class="sidebar-check">
		<label for="btn-menu-izq" class="sidebar-button-izq">☰</label>
		<?php require('includes/vistas/comun/sideBarIzq.php'); ?>

		<main>
			<article>
				<?= $contenidoPrincipal ?>
			</article>
		</main>

		<input type="checkbox" id="btn-menu-der" class="sidebar-check">
		<label for="btn-menu-der" class="sidebar-button-der">🛒</label>
		<?php require('includes/vistas/comun/sideBarDer.php'); ?>

		<?php require('includes/vistas/comun/pie.php'); ?>
</div>
        <?php  //Si la página tiene scripts extra los incluye
            if (isset($scriptsExtra)) {
                foreach ($scriptsExtra as $script) {
                    echo '<script src="' . RUTA_JS . '/' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>';
                }
            }
        ?>
	</body>
</html>