<?php
use es\ucm\fdi\aw\ofertas\FormularioNuevaOferta;


require_once __DIR__.'/includes/config.php';

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Creamos el formulario de nuevo producto
    $form = new FormularioNuevaOferta();
    $htmlFormNuevaOferta = $form->gestiona();

    //Parametros para la plantilla
    $tituloPagina = "Nueva Oferta";

    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;

    $contenidoPrincipal = <<<EOS
        <h1>Añadir Oferta</h1>
        <p><a href="$rutaApp/admin_ofertas.php">⬅ Volver al listado</a></p>
        $htmlFormNuevaOferta
        <script src="$rutaJs/productos.js"></script>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';