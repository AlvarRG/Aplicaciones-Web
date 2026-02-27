<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: index.php'); exit();
}

$tituloPagina = 'Gestión de Productos';
$conn = Aplicacion::getInstance()->getConexionBd();

$query = "SELECT P.*, C.nombre AS nombre_cat 
          FROM Productos P 
          JOIN Categorias C ON P.id_categoria = C.id";
$rs = $conn->query($query);

$filas = "";
while ($fila = $rs->fetch_assoc()) {
    $valorPrecioFinal = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
    
    // Formateo a 2 decimales
    $pBase = number_format($fila['precio_base'], 2, '.', '');
    $pFinal = number_format($valorPrecioFinal, 2, '.', '');
    
    $disponible = $fila['disponible'] ? "SI" : "NO";
    $ofertado = $fila['ofertado'] ? "Carta" : "Retirado";
    
    $filas .= <<<EOS
        <tr>
            <td style="text-align:center;"><img src="img/productos/{$fila['imagen']}" width="40"></td>
            <td><small>{$fila['nombre']}</small></td>
            <td><small>{$fila['nombre_cat']}</small></td>
            <td style="text-align:right;">{$pBase}€</td>
            <td style="text-align:center;"><small>{$fila['iva']}%</small></td>
            <td style="text-align:right;"><strong>{$pFinal}€</strong></td>
            <td style="text-align:center;"><small>$disponible</small></td>
            <td style="text-align:center;"><small>$ofertado</small></td>
            <td style="white-space: nowrap;">
                <a href="editar_producto.php?id={$fila['id']}"><small>[Edit]</small></a>
                <a href="quitar_producto.php?id={$fila['id']}" style="color:red;" onclick="return confirm('¿Eliminar?')"><small>[Eliminar]</small></a>
            </td>
        </tr>
EOS;
}

$contenidoPrincipal = <<<EOS
    <h1>Gestión de la Carta</h1>
    <p><a href="nuevo_producto.php">➕ Añadir Producto</a></p>
    
    <table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; min-width: 600px; font-family: Arial, sans-serif;">
            <thead style="background-color: #eee;">
                <tr>
                    <th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Base</th><th>IVA</th>
                    <th>Total</th><th>Stock</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>$filas</tbody>
        </table>
EOS;

require 'includes/vistas/plantillas/plantilla.php';