<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Categoria;

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Consulta para obtener todas las categorías
    $categorias = Categoria::todas();

    //Si la consulta anterior ha devuelto algo, recorremos las categorías devueltas y construimos las filas de la tabla
    $filas = "";
    if(!empty($categorias)) {
        foreach ($categorias as $fila) {
            $filas .= <<<EOS
                <tr>
                    <td><img src="img/categorias/{$fila['imagen']}" width="100"></td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['descripcion']}</td>
                    <td>
                        <a href="editar_categoria.php?id={$fila['id']}">[Editar]</a>
                        <a href="includes/borrar_categoria.php?id={$fila['id']}" class="boton-borrar" data-mensaje="¡OJO! Esto borrará la categoría permanentemente. ¿Proceder?">[Borrar]</a>
                    </td>
                </tr>
            EOS;
        }
    }

    //Parametros para la plantilla
    $estilosExtra = ['admin_categorias.css'];

    $tituloPagina = 'Gestión de Categorías';

    $contenidoPrincipal = <<<EOS
        <h1>Categorías de Productos</h1>
        <p><a href="nueva_categoria.php">Añadir Categoría</a></p>
        <table>
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
        <script src="js/confirmacion_borrado.js"></script>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';