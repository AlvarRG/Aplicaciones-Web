<?php
// Archivo: includes/vistas/plantilla.php

require_once __DIR__ . '/comun/cabecera.php';
require_once __DIR__ . '/comun/sidebarIzq.php';
?>

<main>
    <?php if (isset($tituloPagina)): ?>
        <h2><?php echo $tituloPagina; ?></h2>
    <?php endif; ?>

    <?php echo $contenidoPrincipal; ?>
</main>

<?php
require_once __DIR__ . '/comun/sidebarDer.php';
require_once __DIR__ . '/comun/pie.php';
?>