<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\productos\Producto;

function renderTituloCategoria($categoria) {
    return "<h2 class='carta-categoria-titulo'>{$categoria}</h2>";
}

function renderTarjetaProducto($fila, $rutaImgs, $rutaApp) {
    $precioFinal = number_format($fila->getPrecioConIva(), 2, ',', '');
    $imagen = $fila->getImagen();
    $nombre = $fila->getNombre();
    $id = $fila->getId();

    return <<<HTML
        <div class="carta-producto">
            <img src="{$rutaImgs}/productos/{$imagen}" class="carta-producto-imagen">
            <h3 class="carta-producto-nombre">{$nombre}</h3>
            <p class="carta-producto-precio">{$precioFinal} €</p>
            <form action="$rutaApp/includes/procesar_carrito.php" method="POST">
                <input type="hidden" name="id_producto" value="{$id}">
                <input type="hidden" name="accion" value="add">
                <label>Cant: <input type="number" name="cantidad" value="1" min="1" max="10" class="carta-producto-cantidad"></label>
                <br>
                <button type="submit" class="carta-boton-anadir">Añadir al pedido</button>
            </form>
        </div>
HTML;
}

//Inicializamos el carrito en sesión si el usuario aún no tiene ninguno
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

//Calculamos cuántos artículos hay en el carrito para mostrarlos en el botón de acceso
$cantidadEnCarrito = 0;
foreach ($_SESSION['carrito'] as $id => $datos) {
    $cantidadEnCarrito += is_array($datos) ? $datos['total'] : $datos;
}

//Obtenemos todos los productos ofertados junto con su categoría, ordenados por categoría y nombre
$productosCarta = Producto::todosOfertados();

$rutaImgs = RUTA_IMGS;
$rutaApp = RUTA_APP;

//Construimos el HTML de la carta agrupando los productos por categoría
$cartaHTML    = "";
$categoriaActual = "";

if (!empty($productosCarta)) {
    $cartaHTML .= "<div class='carta-contenedor'>";

    foreach ($productosCarta as $fila) {

        //Cada vez que cambia la categoría insertamos un título de sección
        if ($categoriaActual !== $fila->getNombreCategoria()) {
            $categoriaActual = $fila->getNombreCategoria();
            $cartaHTML .= renderTituloCategoria($categoriaActual);
        }

        //Tarjeta del producto con su formulario para añadir al carrito
        $cartaHTML .= renderTarjetaProducto($fila, $rutaImgs, $rutaApp);
    }

    $cartaHTML .= "</div>";
} else {
    $cartaHTML = "<p>Lo sentimos, no hay productos disponibles en la carta ahora mismo.</p>";
}

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