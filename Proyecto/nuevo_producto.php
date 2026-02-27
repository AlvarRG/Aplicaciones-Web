<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioNuevoProducto;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$tituloPagina = 'Nuevo Producto';

$form = new FormularioNuevoProducto();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
    <h1>Añadir Producto a la Carta</h1>
    <p><a href="admin_productos.php">⬅ Volver al listado</a></p>
    $htmlFormulario
    <script>
        const pBase = document.getElementById('p_base');
        const pIva = document.getElementById('p_iva');
        const pFinal = document.getElementById('p_final');

        function calcularTotal() {
            const base = parseFloat(pBase.value) || 0;
            const iva = parseInt(pIva.value);
            const total = base + (base * (iva / 100));
            pFinal.innerText = total.toFixed(2);
        }

        // Para que se calcule la primera vez que carga la página
        calcularTotal(); 

        pBase.addEventListener('input', calcularTotal);
        pIva.addEventListener('change', calcularTotal);
    </script>
EOS;

require 'includes/vistas/plantillas/plantilla.php';