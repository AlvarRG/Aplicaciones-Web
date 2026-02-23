<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$conn = conexionBD();
$tituloPagina = 'Nuevo Producto';

// Necesitamos las categorías para el selector (Requisito de usabilidad)
$resCat = $conn->query("SELECT id, nombre FROM Categorias");
$selectorCategorias = '<select name="id_categoria" required>';
while($cat = $resCat->fetch_assoc()) {
    $selectorCategorias .= "<option value='{$cat['id']}'>{$cat['nombre']}</option>";
}
$selectorCategorias .= '</select>';

$contenidoPrincipal = <<<EOS
    <h1>Añadir Producto a la Carta</h1>
    <form action="procesarNuevoProducto.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Información General</legend>
            <p>Nombre: <input type="text" name="nombre" required></p>
            <p>Categoría: $selectorCategorias</p>
            <p>Descripción:<br><textarea name="descripcion"></textarea></p>
        </fieldset>

        <fieldset>
            <legend>Precio y Tasas</legend>
            <p>Precio Base (€): <input type="number" step="0.01" id="p_base" name="precio_base" required></p>
            <p>IVA (%): 
                <select id="p_iva" name="iva">
                    <option value="4">4% (Superreducido)</option>
                    <option value="10" selected>10% (Hostelería)</option>
                    <option value="21">21% (General)</option>
                </select>
            </p>
            <p style="font-size: 1.2em; color: green;">
                <strong>Precio Final (con IVA): <span id="p_final">0.00</span> €</strong>
            </p>
        </fieldset>

        <fieldset>
            <legend>Imagen y Disponibilidad</legend>
            <p>Imágenes: <input type="file" name="imagen" accept="image/*"></p>
            <p><label><input type="checkbox" name="disponible" checked> ¿Hay stock disponible?</label></p>
        </fieldset>

        <button type="submit">Dar de alta producto</button>
    </form>

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

        pBase.addEventListener('input', calcularTotal);
        pIva.addEventListener('change', calcularTotal);
    </script>
EOS;

require 'includes/vistas/plantillas/plantilla.php';