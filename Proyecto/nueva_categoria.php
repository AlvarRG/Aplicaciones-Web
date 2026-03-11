<?php
use es\ucm\fdi\aw\FormularioNuevaCategoria;

require_once __DIR__.'/includes/config.php';

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Creamos el formulario de nueva categoría
    $form = new FormularioNuevaCategoria();
    $htmlFormNuevaCategoria = $form->gestiona();

    //Parametros para la plantilla
    $tituloPagina = "Nueva Categoría";

    $contenidoPrincipal = <<<EOS
        <h1>Crear Categoría</h1>
        <p><a href="admin_categorias.php">⬅ Volver al listado</a></p>
        $htmlFormNuevaCategoria
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';