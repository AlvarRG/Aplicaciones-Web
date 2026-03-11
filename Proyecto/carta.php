<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Producto;

//Inicializamos el carrito en sesión si el usuario aún no tiene ninguno
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

//Obtenemos todos los productos ofertados junto con su categoría, ordenados por categoría y nombre
$productosCarta = Producto::todosOfertados();

//Construimos el HTML de la carta agrupando los productos por categoría
$cartaHTML    = "";
$categoriaActual = "";

if (!empty($productosCarta)) {
    $cartaHTML .= "<div class='carta-contenedor'>";

    foreach ($productosCarta as $fila) {

        //Cada vez que cambia la categoría insertamos un título de sección
        if ($categoriaActual !== $fila['nombre_cat']) {
            $categoriaActual = $fila['nombre_cat'];
            $cartaHTML .= "<h2 class='carta-categoria-titulo'>{$categoriaActual}</h2>";
        }

        //Calculamos el precio final aplicando el IVA al precio base
        $precioFinal = number_format(Producto::calcularPrecioConIva((float)$fila['precio_base'], (int)$fila['iva']), 2, ',', '');

        //Tarjeta del producto con su formulario para añadir al carrito
        $cartaHTML .= <<<EOS
            <div class="carta-producto">
                <img src="img/productos/{$fila['imagen']}" class="carta-producto-imagen">
                <h3 class="carta-producto-nombre">{$fila['nombre']}</h3>
                <p class="carta-producto-precio">{$precioFinal} €</p>
                <form action="includes/procesar_carrito.php" method="POST">
                    <input type="hidden" name="id_producto" value="{$fila['id']}">
                    <input type="hidden" name="accion" value="add">
                    <label>Cant: <input type="number" name="cantidad" value="1" min="1" max="10" class="carta-producto-cantidad"></label>
                    <br>
                    <button type="submit" class="carta-boton-anadir">Añadir al pedido</button>
                </form>
            </div>
        EOS;
    }

    $cartaHTML .= "</div>";
} else {
    $cartaHTML = "<p>Lo sentimos, no hay productos disponibles en la carta ahora mismo.</p>";
}

//Calculamos cuántos artículos hay en el carrito para mostrarlos en el botón de acceso
$cantidadEnCarrito = array_sum($_SESSION['carrito']);

//Parámetros para la plantilla
$tituloPagina = 'Nuestra Carta';
$estilosExtra = ['carta.css'];

$contenidoPrincipal = <<<EOS
    <div class="carta-header">
        <h1>Nuestra Carta</h1>
        <a href="carrito.php" class="carta-link-carrito">
            Mi Pedido ({$cantidadEnCarrito})
        </a>
    </div>
    $cartaHTML
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';