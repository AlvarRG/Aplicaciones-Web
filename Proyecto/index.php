<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Categoria;

//Obtenemos las categorías para mostrarlas como tarjetas en la portada
$categorias = Categoria::todas();

//Construimos las tarjetas de categorías
$tarjetas = "";
if (!empty($categorias)) {
    foreach ($categorias as $cat) {
        $tarjetas .= <<<EOS
            <a href="carta.php" class="index-categoria-card">
                <img src="img/categorias/{$cat['imagen']}" class="index-categoria-img" alt="{$cat['nombre']}">
                <p class="index-categoria-nombre">{$cat['nombre']}</p>
            </a>
        EOS;
    }
}

//Parámetros para la plantilla
$tituloPagina = 'Bistro FDI';
$estilosExtra = ['index.css'];

$contenidoPrincipal = <<<EOS
    <!-- Hero Banner -->
    <div class="index-hero">
        <img src="img/Banner.jpg" class="index-hero-img" alt="Bistro FDI">
        <div class="index-hero-overlay">
            <h1 class="index-hero-titulo">Bienvenido a Bistro FDI</h1>
            <p class="index-hero-subtitulo">Cocina de autor en el corazón de la facultad</p>
            <a href="carta.php" class="index-hero-cta">Ver la Carta</a>
        </div>
    </div>

    <!-- Categorías -->
    <h2 class="index-sección-titulo">Nuestra Carta</h2>
    <div class="index-categorias-grid">
        $tarjetas
    </div>

    <!-- Cómo funciona -->
    <h2 class="index-sección-titulo">¿Cómo funciona?</h2>
    <div class="index-pasos">
        <div class="index-paso">
            <span class="index-paso-icono">🍽️</span>
            <p class="index-paso-titulo">Elige tu pedido</p>
            <p class="index-paso-desc">Explora nuestra carta y añade lo que quieras al carrito.</p>
        </div>
        <div class="index-paso">
            <span class="index-paso-icono">💳</span>
            <p class="index-paso-titulo">Paga como prefieras</p>
            <p class="index-paso-desc">Con tarjeta online o en efectivo al camarero.</p>
        </div>
        <div class="index-paso">
            <span class="index-paso-icono">✅</span>
            <p class="index-paso-titulo">Recibe tu pedido</p>
            <p class="index-paso-desc">Cocina lo prepara y te lo sirve en tu mesa.</p>
        </div>
    </div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';