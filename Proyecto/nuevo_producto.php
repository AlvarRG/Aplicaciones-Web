<?php
use es\ucm\fdi\aw\FormularioNuevoProducto;

require_once __DIR__.'/includes/config.php';

// Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    // Creamos el formulario de nuevo producto
    $form = new FormularioNuevoProducto();
    $htmlFormNuevoProducto = $form->gestiona();

    // Parametros para la plantilla
    $tituloPagina = "Nuevo Producto";

    $contenidoPrincipal = <<<EOS
        <h1>Añadir Producto a la Carta</h1>
        <p><a href="admin_productos.php">⬅ Volver al listado</a></p>
        $htmlFormNuevoProducto
        <script src="js/productos.js"></script>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';