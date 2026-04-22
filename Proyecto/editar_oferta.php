<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\ofertas\Oferta;
use es\ucm\fdi\aw\ofertas\FormularioEditarOferta;

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    //Obtenemos el nombre de la oferta a editar. Este dato solo lo usaremos para montar el contenido principal de la página
    $oferta = Oferta::porId((int)$id);

    //Creamos el formulario de edición
    $form = new FormularioEditarOferta($id);
    $htmlFormulario = $form->gestiona();

    //Parametros para la plantilla
    $tituloPagina = "Editar Oferta";
    
    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;

    $nombreOferta = $oferta ? $oferta->getNombre() : '';

    $contenidoPrincipal = <<<EOS
        <h1>Editar Oferta: {$nombreOferta}</h1>
        <p><a href="$rutaApp/admin_ofertas.php">⬅ Volver al listado</a></p>
        $htmlFormulario
		<script src="$rutaJs/nueva:oferta.js"></script>
EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';