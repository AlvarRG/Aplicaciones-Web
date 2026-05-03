<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\ofertas\Oferta;

/**
 * Renderiza el bloque HTML de un producto individual dentro de una oferta.
 */
function renderProductoOferta($prod, $rutaImgs) {
    $rutaImg = "{$rutaImgs}/productos/{$prod['imagen']}";
    return <<<HTML
        <div class="oferta-item-producto">
            <img src="{$rutaImg}" alt="{$prod['nombre']}" class="mini-imagen-producto">
            <span>{$prod['nombre']} (x{$prod['cantidad']})</span>
        </div>
HTML;
}

/**
 * Renderiza una tarjeta de oferta completa.
 */
function renderTarjetaOferta($nombre, $descripcion, $productosHTML, $pvpBase, $pvpFinal) {
    // He añadido etiquetas <del> y <strong> para resaltar mejor el descuento visualmente
    return <<<HTML
        <div class="carta-oferta">
            <h3 class="carta-oferta-nombre">{$nombre}</h3>
            <p class="carta-oferta-descripcion">{$descripcion}</p>
            <div class="carta-oferta-productos">
                {$productosHTML}
            </div>
            <p class="carta-oferta-precioInicial"><del>{$pvpBase} €</del></p>
            <p class="carta-oferta-precioFinal"><strong>{$pvpFinal} €</strong></p>
        </div>
HTML;
}

/**
 * Renderiza el contenedor principal de la página de ofertas.
 */
function renderPaginaOfertas($contenidoOfertas) {
    return <<<HTML
        <div class="oferta-header">
            <h1>Nuestras Ofertas</h1>
        </div>
        {$contenidoOfertas}
HTML;
}

// --- LÓGICA DE CONTROL ---

// Obtenemos todos los productos ofertados junto con su categoría
$ofertasCarta = Oferta::ofertasActivas();
$rutaImgs = RUTA_IMGS;
$fechaActual = new DateTime();

$cartaOfertasHTML = "";

if (!empty($ofertasCarta)) {
    $tarjetasHTML = "";

    foreach ($ofertasCarta as $oferta) {
        $nombre = $oferta->getNombre();
        $descripcion = $oferta->getDescripcion();
        
        $productosDeLaOferta = Oferta::obtenerProductosOferta($oferta->getId());
        
        $htmlProductosArr = [];
        $precioOriginalLote = 0;
        
        // Procesamos los productos de la oferta actual
        foreach ($productosDeLaOferta as $prod) {
            $htmlProductosArr[] = renderProductoOferta($prod, $rutaImgs);
            
            $precioConIva = $prod['precio_base'] * (1 + $prod['iva'] / 100);
            $precioOriginalLote += ($precioConIva * $prod['cantidad']);
        }
        
        // Unimos los productos (al ser divs, ya no necesitamos el <br> que tenías antes)
        $productosIncluidos = implode('', $htmlProductosArr);
        
        // Cálculo del precio final aplicando el descuento
        $descuento = $oferta->getDescuento();
        $precioFinalCalculado = $precioOriginalLote * (1 - ($descuento / 100));
        
        $pvpBaseHTML = number_format($precioOriginalLote, 2, ',', '.');
        $pvpFinalHTML = number_format($precioFinalCalculado, 2, ',', '.');
        
        // Cálculo del estado temporal (En tu código original se calculaba pero no se pintaba. 
        // Lo mantengo calculado por si en el futuro quieres pasarlo a la vista).
        $fechaInicio = new DateTime($oferta->getFechaInicio());
        $fechaFin = new DateTime($oferta->getFechaFin());
        
        $estado = "Inactiva";
        if ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
            $estado = "Activa";
        } elseif ($fechaActual > $fechaFin) {
            $estado = "Caducada";
        }

        // Construimos la tarjeta HTML
        $tarjetasHTML .= renderTarjetaOferta($nombre, $descripcion, $productosIncluidos, $pvpBaseHTML, $pvpFinalHTML);
    }

    $cartaOfertasHTML = "<div class='carta-contenedor'>{$tarjetasHTML}</div>";
} else {
    $cartaOfertasHTML = "<p>Lo sentimos, no hay ofertas disponibles ahora mismo.</p>";
}

// Configuración de la página
$tituloPagina = 'Nuestras Ofertas';
$estilosExtra = ['carta_ofertas.css'];

// Generamos el contenido principal usando la función contenedora
$contenidoPrincipal = renderPaginaOfertas($cartaOfertasHTML);

require __DIR__.'/includes/vistas/plantillas/plantilla.php';