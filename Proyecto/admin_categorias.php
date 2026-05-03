<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\categorias\Categoria;

function renderFilaCategoria($cat, $rutaApp, $rutaImgs) {
    $id = $cat->getId();
    $nombre = $cat->getNombre();
    $descripcion = $cat->getDescripcion();
    $imagen = $cat->getImagen();

    return <<<HTML
        <tr>
            <td><img src="{$rutaImgs}/categorias/{$imagen}" width="100"></td>
            <td>{$nombre}</td>
            <td>{$descripcion}</td>
            <td class="admin-categorias-acciones">
                <a href="$rutaApp/editar_categoria.php?id={$id}"><img src="{$rutaImgs}/edit.png" width="30" alt="Editar"></a>
                <a href="$rutaApp/includes/borrar_categoria.php?id={$id}" class="admin-categorias-eliminar boton-borrar" data-mensaje="¡OJO! Esto borrará la categoría permanentemente. ¿Proceder?"><img src="{$rutaImgs}/borrar.png" width="30" alt="Borrar"></a>
            </td>
        </tr>
HTML;
}

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Consulta para obtener todas las categorías
    $categorias = Categoria::todas();

    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;
    $rutaImgs = RUTA_IMGS;

    //Si la consulta anterior ha devuelto algo, recorremos las categorías devueltas y construimos las filas de la tabla
    $filas = "";
    if(!empty($categorias)) {
        foreach ($categorias as $cat) {
            $filas .= renderFilaCategoria($cat, $rutaApp, $rutaImgs);
        }
    }

    //Parametros para la plantilla
    $estilosExtra = ['admin_categorias.css'];

    $tituloPagina = 'Gestión de Categorías';

    $contenidoPrincipal = <<<EOS
        <h1>Categorías de Productos</h1>
        <p><a href="$rutaApp/nueva_categoria.php">Añadir Categoría</a></p>
        <table class="admin-categorias-tabla">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>$filas</tbody>
        </table>
        <script src="$rutaJs/confirmacion_borrado.js"></script>
EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';