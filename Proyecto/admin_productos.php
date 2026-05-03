<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\productos\Producto;

function renderFilaProducto($fila, $rutaApp, $rutaImgs) {
    $precioBase  = number_format($fila->getPrecioBase(), 2, ',', '');
    $precioFinal = number_format($fila->getPrecioConIva(), 2, ',', '');
    $disponible = $fila->getDisponible() ? "SI" : "NO";
    $ofertado   = $fila->getOfertado()   ? "Carta" : "Retirado";
    $id = $fila->getId();
    $nombre = $fila->getNombre();
    $categoria = $fila->getNombreCategoria();
    $iva = $fila->getIva();
    $imagen = $fila->getImagen();

    return <<<HTML
        <tr>
            <td class="admin-productos-img"><img src="{$rutaImgs}/productos/{$imagen}" width="100"></td>
            <td>{$nombre}</td>
            <td>{$categoria}</td>
            <td class="admin-productos-numero">$precioBase €</td>
            <td class="admin-productos-numero">{$iva}%</td>
            <td class="admin-productos-numero"><strong>$precioFinal €</strong></td>
            <td class="admin-productos-centro">$disponible</td>
            <td class="admin-productos-centro">$ofertado</td>
            <td class="admin-productos-acciones">
                <a href="$rutaApp/editar_producto.php?id={$id}"><img src="{$rutaImgs}/edit.png" width="30" alt="Editar"></a>
                <a href="$rutaApp/includes/borrar_producto.php?id={$id}" class="admin-productos-eliminar boton-borrar" data-mensaje="¿Estás seguro? Borrará este producto permanentemente."><img src="{$rutaImgs}/borrar.png" width="30" alt="Eliminar"></a>
            </td>
        </tr>
HTML;
}

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Consulta para obtener todos los productos
    $productos = Producto::todosConCategoria();

    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;
    $rutaImgs = RUTA_IMGS;

    //Si la consulta anterior ha devuelto algo, recorremos los productos devueltos y construimos las filas de la tabla
    $filas = "";
    if(!empty($productos)) {
        foreach ($productos as $fila) {
            $filas .= renderFilaProducto($fila, $rutaApp, $rutaImgs);
        }
    }

    //Parámetros para la plantilla
    $estilosExtra = ['admin_productos.css'];

    $tituloPagina = 'Gestión de Productos';

    $contenidoPrincipal = <<<EOS
        <h1>Gestión de la Carta</h1>
        <p><a href="$rutaApp/nuevo_producto.php">Añadir Producto</a></p>
        <table class="admin-productos-tabla">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Base</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>$filas</tbody>
        </table>
        <script src="$rutaJs/confirmacion_borrado.js"></script>
EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';