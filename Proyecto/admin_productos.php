<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: index.php'); exit();
}

$tituloPagina = 'Gestión de Productos';
$estilosExtra = ['admin_productos.css'];
$conn = Aplicacion::getInstance()->getConexionBd();

$query = "SELECT P.*, C.nombre AS nombre_cat 
          FROM Productos P 
          JOIN Categorias C ON P.id_categoria = C.id";
$rs = $conn->query($query);

$filas = "";
while ($fila = $rs->fetch_assoc()) {
    $valorPrecioFinal = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
    
    $pBase = number_format($fila['precio_base'], 2, '.', '');
    $pFinal = number_format($valorPrecioFinal, 2, '.', '');
    
    $disponible = $fila['disponible'] ? "SI" : "NO";
    $ofertado = $fila['ofertado'] ? "Carta" : "Retirado";
    
    $filas .= <<<EOS
        <tr>
            <td class="admin-productos-img"><img src="img/productos/{$fila['imagen']}" width="40"></td>
            <td><small>{$fila['nombre']}</small></td>
            <td><small>{$fila['nombre_cat']}</small></td>
            <td class="admin-productos-numero">{$pBase}€</td>
            <td class="admin-productos-centro"><small>{$fila['iva']}%</small></td>
            <td class="admin-productos-numero"><strong>{$pFinal}€</strong></td>
            <td class="admin-productos-centro"><small>$disponible</small></td>
            <td class="admin-productos-centro"><small>$ofertado</small></td>
            <td class="admin-productos-acciones">
                <a href="editar_producto.php?id={$fila['id']}"><small>[Edit]</small></a>
                <a href="includes/borrar_producto.php?id={$fila['id']}" class="admin-productos-eliminar boton-borrar" data-mensaje="¿Estás seguro de esta acción? Borrará este producto permanentemente de la base de datos"><small>[Eliminar]</small></a>
            </td>
        </tr>
EOS;
}

$contenidoPrincipal = <<<EOS
    <h1>Gestión de la Carta</h1>
    <p><a href="nuevo_producto.php">Añadir Producto</a></p>
    
    <table class="admin-productos-tabla" border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Base</th><th>IVA</th>
                <th>Total</th><th>Stock</th><th>Estado</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>$filas</tbody>
    </table>
    <script src="js/confirmacion_borrado.js"></script>
EOS;

require 'includes/vistas/plantillas/plantilla.php';