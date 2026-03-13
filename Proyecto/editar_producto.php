<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Producto;
use es\ucm\fdi\aw\FormularioEditarProducto;

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    //Obtenemos el nombre del producto a editar. Este dato, solo lo usaremos para montar el contenido principal de la página
    $product = Producto::porId((int)$id);

    //Creamos el formulario de edición
    $form = new FormularioEditarProducto($id);
    $htmlFormulario = $form->gestiona();

    //Parametros para la plantilla
    $tituloPagina = "Editar Producto";
    
    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;

    $contenidoPrincipal = <<<EOS
        <h1>Editar Producto: {$product['nombre']}</h1>
        <p><a href="$rutaApp/admin_productos.php">⬅ Volver al listado</a></p>
        $htmlFormulario
        <script src="$rutaJs/productos.js"></script>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';