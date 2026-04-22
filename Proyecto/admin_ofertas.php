<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\ofertas\Oferta;


//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Consulta para obtener todas las ofertas
    $ofertas = Oferta::todasLasOfertas();

    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;
    $rutaImgs = RUTA_IMGS;

    //Si la consulta anterior ha devuelto algo, recorremos las ofertas devueltas y construimos las filas de la tabla
    $filas = "";
    if(!empty($ofertas)) {
        foreach ($ofertas as $fila) {
			$nombre = $fila->getNombre();
			$descripcion = $fila->getDescripcion();
			
			$productosDeLaOferta = Oferta::obtenerProductosOferta($fila->getId());
            $textosProductos = [];
            $precioOriginalLote = 0;
            foreach ($productosDeLaOferta as $prod) {
                $textosProductos[] = $prod['nombre'] . ' (x' . $prod['cantidad'] . ')'; 
                $precioConIva = $prod['precio_base'] * (1 + $prod['iva'] / 100);
                $precioOriginalLote += ($precioConIva * $prod['cantidad']);
            }
			$productosIncluidos = implode(', <br>', $textosProductos);
			
            $fechaActual = new DateTime();
            $fechaInicio = new DateTime($fila->getFechaInicio());
            $fechaFin = new DateTime($fila->getFechaFin());
			
			$descuento = $fila->getDescuento();
			$precioFinalCalculado = $precioOriginalLote * (1 - ($descuento / 100));
            $pvpBaseHTML = number_format($precioOriginalLote, 2, ',', '.');
            $pvpFinalHTML = number_format($precioFinalCalculado, 2, ',', '.');
			
			$estado = "Inactiva";
            if ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
                $estado = "Activa";
            } elseif ($fechaActual > $fechaFin) {
                $estado = "Caducada";
            }

            $filas .= <<<EOS
                <tr>
                    <td>{$nombre}</td>
                    <td>{$descripcion}</td>
                    <td>{$productosIncluidos}</td>
                    <td class="admin-ofertas-centro">{$fechaInicio->format('d/m/Y')}</td>
                    <td class="admin-ofertas-centro">{$fechaFin->format('d/m/Y')}</td>
                    <td class="admin-ofertas-numero">{$descuento}%</td>
                    <td class="admin-ofertas-numero"><del>{$pvpBaseHTML}€</del> <br/> <strong>{$pvpFinalHTML}€</strong></td>
                    <td class="admin-ofertas-centro">{$estado}</td>
                    <td class="admin-ofertas-acciones">
                        <a href="$rutaApp/editar_oferta.php?id={$fila->getId()}">[Editar]</a>
                        <a href="$rutaApp/includes/borrar_oferta.php?id={$fila->getId()}" class="admin-ofertas-eliminar boton-borrar" data-mensaje="¿Estás seguro? Borrará esta oferta permanentemente.">[Eliminar]</a>
                    </td>
                </tr>
            EOS;
        }
    }

    //Parámetros para la plantilla
    $estilosExtra = ['admin_ofertas.css'];

    $tituloPagina = 'Gestión de ofertas';

    $contenidoPrincipal = <<<EOS
        <h1>Gestión de ofertas</h1>
        <p><a href="$rutaApp/nueva_oferta.php">Añadir Oferta</a></p>
        <table class="admin-ofertas-tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Productos (Cant.)</th>
                    <th>Comienzo</th>
                    <th>Fin</th>
                    <th>Descuento</th>
                    <th>Precio de lote</th>
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