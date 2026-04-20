<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\ofertas\Oferta;

//Obtenemos todos los productos ofertados junto con su categoría, ordenados por categoría y nombre
$ofertasCarta = Oferta::todasLasOfertas();

//Construimos el HTML de la carta agrupando los productos por categoría
$cartaOfertasHTML    = "";

if (!empty($ofertasCarta)) {
    $cartaOfertasHTML .= "<div class='carta-contenedor'>";

    foreach ($ofertasCarta as $fila) {

        $nombre = $fila['nombre'];
			$descripcion = $fila['descripcion'];
			
			$productosDeLaOferta = Oferta::obtenerProductosOferta($fila['id']);
            $textosProductos = [];
            $precioOriginalLote = 0;
			$rutaImgs = RUTA_IMGS;
            foreach ($productosDeLaOferta as $prod) {
                $rutaImg = "{$rutaImgs}/productos/{$prod['imagen']}";
				$textosProductos[] = <<<HTML
					<div class="oferta-item-producto">
						<img src="{$rutaImg}" alt="{$prod['nombre']}" class="mini-imagen-producto">
						<span>{$prod['nombre']} (x{$prod['cantidad']})</span>
					</div>
				HTML;
                $precioConIva = $prod['precio_base'] * (1 + $prod['iva'] / 100);
                $precioOriginalLote += ($precioConIva * $prod['cantidad']);
            }
			$productosIncluidos = implode('<br>', $textosProductos);
			
            $fechaActual = new DateTime();
            $fechaInicio = new DateTime($fila['fecha_inicio']);
            $fechaFin = new DateTime($fila['fecha_fin']);
			
			$descuento = $fila['descuento'];
			$precioFinalCalculado = $precioOriginalLote * (1 - ($descuento / 100));
            $pvpBaseHTML = number_format($precioOriginalLote, 2, ',', '.');
            $pvpFinalHTML = number_format($precioFinalCalculado, 2, ',', '.');
			
			$estado = "Inactiva";
            if ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
                $estado = "Activa";
            } elseif ($fechaActual > $fechaFin) {
                $estado = "Caducada";
            }

        //Tarjeta del producto con su formulario para añadir al carrito
        $cartaOfertasHTML .= <<<EOS
            <div class="carta-oferta">
                <h3 class="carta-oferta-nombre">{$fila['nombre']}</h3>
				<p class="carta-oferta-descripcion">{$descripcion}</p>
				<p class="carta-oferta-productos">{$productosIncluidos}</p>
				<p class="carta-oferta-precioInicial">{$pvpBaseHTML} €</p>
                <p class="carta-oferta-precioFinal">{$pvpFinalHTML} €</p>
            </div>
EOS;
    }

    $cartaOfertasHTML .= "</div>";
} else {
    $cartaOfertasHTML = "<p>Lo sentimos, no hay ofertas disponibles ahora mismo.</p>";
}

//Parámetros para la plantilla
$tituloPagina = 'Nuestras Ofertas';
$estilosExtra = ['carta_ofertas.css'];

$contenidoPrincipal = <<<EOS
    <div class="oferta-header">
        <h1>Nuestras Ofertas</h1>
    </div>
    $cartaOfertasHTML
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';