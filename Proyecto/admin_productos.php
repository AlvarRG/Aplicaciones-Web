<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Producto;

// Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    // Consulta para obtener todos los productos
    $productos = Producto::todosConCategoria();

    // Si la consulta anterior ha devuelto algo, recorremos los productos devueltos y construimos las filas de la tabla
    $filas = "";
    if(!empty($productos)) {
        foreach ($productos as $fila) {
            $precioBase  = number_format($fila['precio_base'], 2, ',', '');
            $precioFinal = number_format($fila['precio_base'] * (1 + $fila['iva'] / 100), 2, ',', '');
            $disponible = $fila['disponible'] ? "SI" : "NO";
            $ofertado   = $fila['ofertado']   ? "Carta" : "Retirado";

            $filas .= <<<EOS
                <tr>
                    <td><img src="img/productos/{$fila['imagen']}" width="100"></td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['nombre_cat']}</td>
                    <td>$precioBase €</td>
                    <td>{$fila['iva']}%</td>
                    <td><strong>$precioFinal €</strong></td>
                    <td>$disponible</td>
                    <td>$ofertado</td>
                    <td>
                        <a href="editar_producto.php?id={$fila['id']}">[Editar]</a>
                        <a href="includes/borrar_producto.php?id={$fila['id']}" class="boton-borrar" data-mensaje="¿Estás seguro? Borrará este producto permanentemente.">[Eliminar]</a>
                    </td>
                </tr>
            EOS;
        }
    }

    // Parámetros para la plantilla
    $estilosExtra = ['admin_productos.css'];

    $tituloPagina = 'Gestión de Productos';

    $contenidoPrincipal = <<<EOS
        <h1>Gestión de la Carta</h1>
        <p><a href="nuevo_producto.php">Añadir Producto</a></p>
        <table>
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
        <script src="js/confirmacion_borrado.js"></script>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';