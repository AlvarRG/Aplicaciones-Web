<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Nuestra Carta';
$conn = Aplicacion::getInstance()->getConexionBd();

// Inicializamos el carrito en la sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Obtenemos solo los productos que están "ofertados" (en la carta)
// y los ordenamos por categoría para que tenga sentido visualmente
$query = "SELECT P.*, C.nombre AS nombre_cat 
          FROM Productos P 
          JOIN Categorias C ON P.id_categoria = C.id 
          WHERE P.ofertado = 1 
          ORDER BY C.nombre, P.nombre";
$rs = $conn->query($query);

$cartaHTML = "";
$categoriaActual = "";

if ($rs && $rs->num_rows > 0) {
    $cartaHTML .= "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
    
    while ($fila = $rs->fetch_assoc()) {
        // Mostrar separador de categoría si cambiamos de categoría
        if ($categoriaActual != $fila['nombre_cat']) {
            $categoriaActual = $fila['nombre_cat'];
            $cartaHTML .= "<h2 style='width: 100%; border-bottom: 2px solid #303030; margin-top: 20px;'>{$categoriaActual}</h2>";
        }

        // Calcular precio final
        $precioFinal = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
        $pFinalFmt = number_format($precioFinal, 2, '.', '');
        
        // Bloque del producto
        $cartaHTML .= <<<EOS
        <div style="border: 1px solid #ccc; border-radius: 8px; padding: 15px; width: 250px; text-align: center; background-color: #f9f9f9;">
            <img src="img/productos/{$fila['imagen']}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 5px;">
            <h3 style="margin: 10px 0 5px 0;">{$fila['nombre']}</h3>
            <p style="font-size: 1.2em; color: #28a745; font-weight: bold; margin: 10px 0;">{$pFinalFmt} €</p>
            
            <form action="procesar_carrito.php" method="POST">
                <input type="hidden" name="id_producto" value="{$fila['id']}">
                <input type="hidden" name="accion" value="add">
                
                <label>Cant: <input type="number" name="cantidad" value="1" min="1" max="10" style="width: 50px; display: inline-block;"></label>
                <br>
                <button type="submit" style="background-color: #303030; color: white; padding: 10px; width: 100%; border: none; border-radius: 5px; margin-top: 10px; font-weight: bold;">
                    🛒 Añadir al pedido
                </button>
            </form>
        </div>
EOS;
    }
    $cartaHTML .= "</div>";
} else {
    $cartaHTML = "<p>Lo sentimos, no hay productos disponibles en la carta ahora mismo.</p>";
}

// Botón flotante o superior para ir al carrito
$cantidadEnCarrito = array_sum($_SESSION['carrito']); // Suma de todas las cantidades

$contenidoPrincipal = <<<EOS
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>🍽️ Nuestra Carta</h1>
        <a href="carrito.php" style="background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 1.1em;">
            🛒 Mi Pedido ({$cantidadEnCarrito})
        </a>
    </div>
    
    $cartaHTML
EOS;

require 'includes/vistas/plantillas/plantilla.php';