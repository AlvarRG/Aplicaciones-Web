<?php
require_once __DIR__.'/utils.php';
session_start();

// 1. Seguridad: Solo el Gerente
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: index.php');
    exit();
}

$conn = conexionBD();
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    header('Location: admin_productos.php');
    exit();
}

// 2. Obtener datos del producto
$query = "SELECT * FROM Productos WHERE id = $id";
$rs = $conn->query($query);
$product = $rs->fetch_assoc();

if (!$product) {
    die("Producto no encontrado.");
}

$tituloPagina = "Editar Producto: " . htmlspecialchars($product['nombre']);

// 3. Preparar Selectores (Lógica fuera del EOS para evitar errores de sintaxis)
// Selector de Categorías
$resCat = $conn->query("SELECT id, nombre FROM Categorias");
$selectorCategorias = '<select name="id_categoria" required>';
while($cat = $resCat->fetch_assoc()) {
    $selected = ($cat['id'] == $product['id_categoria']) ? 'selected' : '';
    $selectorCategorias .= "<option value='{$cat['id']}' $selected>{$cat['nombre']}</option>";
}
$selectorCategorias .= '</select>';

// Atributos de IVA
$sel4  = ($product['iva'] == 4) ? 'selected' : '';
$sel10 = ($product['iva'] == 10) ? 'selected' : '';
$sel21 = ($product['iva'] == 21) ? 'selected' : '';

// Checkboxes de estado
$dispChecked = ($product['disponible'] == 1) ? 'checked' : '';
$oferChecked = ($product['ofertado'] == 1) ? 'checked' : '';

// 4. Montar el contenido de la vista
$contenidoPrincipal = <<<EOS
    <h1>Editar Producto</h1>
    <p><a href="admin_productos.php">⬅ Volver al listado</a></p>

    <form action="procesarEditarProducto.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="{$product['id']}">
        
        <fieldset>
            <legend>Datos Principales</legend>
            <p>Nombre: <input type="text" name="nombre" value="{$product['nombre']}" required></p>
            <p>Categoría: $selectorCategorias</p>
            <p>Descripción:<br>
               <textarea name="descripcion" rows="4" cols="50">{$product['descripcion']}</textarea>
            </p>
        </fieldset>

        <fieldset>
            <legend>Precio y Tasas</legend>
            <p>Precio Base (€): 
               <input type="number" step="0.01" id="p_base" name="precio_base" value="{$product['precio_base']}" required>
            </p>
            <p>IVA (%): 
                <select id="p_iva" name="iva">
                    <option value="4" $sel4>4% (Superreducido)</option>
                    <option value="10" $sel10>10% (Hostelería)</option>
                    <option value="21" $sel21>21% (General)</option>
                </select>
            </p>
            <p style="font-size: 1.2em; color: green;">
                <strong>Precio Final Actualizado: <span id="p_final">0.00</span> €</strong>
            </p>
        </fieldset>

        <fieldset>
            <legend>Imagen y Disponibilidad</legend>
            <p>Imagen actual:<br>
               <img src="img/productos/{$product['imagen']}" width="100" style="border: 1px solid #ccc; margin: 5px 0;">
            </p>
            <p>Cambiar imagen: <input type="file" name="imagen" accept="image/*"></p>
            
            <p><label><input type="checkbox" name="disponible" $dispChecked> Producto en Stock (Disponible)</label></p>
            <p><label><input type="checkbox" name="ofertado" $oferChecked> Visible en Carta (Ofertado)</label></p>
        </fieldset>

        <div style="margin-top: 20px;">
            <button type="submit">Actualizar Producto</button>
            <a href="eliminar_producto_fisico.php?id={$product['id']}" 
               style="color: red; margin-left: 20px;" 
               onclick="return confirm('¿ESTÁS SEGURO? Esta acción eliminará el producto para siempre de la base de datos.')">
               Eliminar Permanentemente
            </a>
        </div>
    </form>

    <script src="js/productos.js"></script>
EOS;

require 'includes/vistas/plantillas/plantilla.php';