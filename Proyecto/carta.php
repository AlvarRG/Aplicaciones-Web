<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Nuestra Carta';
$estilosExtra = ['carta.css'];
$conn = Aplicacion::getInstance()->getConexionBd();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$query = "SELECT P.*, C.nombre AS nombre_cat 
          FROM Productos P 
          JOIN Categorias C ON P.id_categoria = C.id 
          WHERE P.ofertado = 1 
          ORDER BY C.nombre, P.nombre";
$rs = $conn->query($query);

$cartaHTML = "";
$categoriaActual = "";

if ($rs && $rs->num_rows > 0) {
    $cartaHTML .= "<div class='carta-contenedor'>";

    while ($fila = $rs->fetch_assoc()) {
        if ($categoriaActual != $fila['nombre_cat']) {
            $categoriaActual = $fila['nombre_cat'];
            $cartaHTML .= "<h2 class='carta-categoria-titulo'>{$categoriaActual}</h2>";
        }

        $precioFinal = $fila['precio_base'] * (1 + ($fila['iva'] / 100));
        $pFinalFmt = number_format($precioFinal, 2, '.', '');

        $cartaHTML .= <<<EOS
        <div class="carta-producto">
            <img src="img/productos/{$fila['imagen']}" class="carta-producto-imagen">
            <h3 class="carta-producto-nombre">{$fila['nombre']}</h3>
            <p class="carta-producto-precio">{$pFinalFmt} €</p>
            
            <form action="procesar_carrito.php" method="POST">
                <input type="hidden" name="id_producto" value="{$fila['id']}">
                <input type="hidden" name="accion" value="add">

                <label>Cant: <input type="number" name="cantidad" value="1" min="1" max="10" class="carta-producto-cantidad"></label>
                <br>
                <button type="submit" class="carta-boton-anadir">
                    Añadir al pedido
                </button>
            </form>
        </div>
EOS;
    }
    $cartaHTML .= "</div>";
} else {
    $cartaHTML = "<p>Lo sentimos, no hay productos disponibles en la carta ahora mismo.</p>";
}

$cantidadEnCarrito = array_sum($_SESSION['carrito']);

$contenidoPrincipal = <<<EOS
    <div class="carta-header">
        <h1>Nuestra Carta</h1>
        <a href="carrito.php" class="carta-link-carrito">
            Mi Pedido ({$cantidadEnCarrito})
        </a>
    </div>

    $cartaHTML
EOS;

require 'includes/vistas/plantillas/plantilla.php';