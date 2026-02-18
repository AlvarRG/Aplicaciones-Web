<div class="formulario-producto">
    <h1>Crear / Editar Producto</h1>
    
    <form action="#" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Información Básica</legend>
            <label>Nombre del Producto:</label><br>
            <input type="text" name="nombre" required size="40"><br><br>
            
            <label>Descripción:</label><br>
            <textarea name="descripcion" rows="4" cols="40"></textarea><br><br>
            
            <label>Categoría:</label><br>
            <select name="categoria">
                <option value="1">Entrantes</option>
                <option value="2">Platos Principales</option>
                <option value="3">Bebidas</option>
                <option value="4">Postres</option>
            </select>
        </fieldset>

        <fieldset>
            <legend>Precio e IVA</legend>
            <label>Precio Base (Sin IVA): €</label>
            <input type="number" name="precio" step="0.01" value="0.00" required><br><br>
            
            <label>Tipo de IVA:</label>
            <select name="iva">
                <option value="4">Súper Reducido (4%)</option>
                <option value="10" selected>Reducido (10%)</option>
                <option value="21">General (21%)</option>
            </select><br><br>

            <div style="background-color: #eef; padding: 10px; border: 1px solid #ccc;">
                <strong>Precio Final de Venta: </strong> 
                <span style="font-size: 1.2em; color: #d32f2f;">(Se calculará al guardar)</span>
            </div>
        </fieldset>

        <fieldset>
            <legend>Imágenes y Estado</legend>
            <label>Subir Imagen(es):</label>
            <input type="file" name="imagenes[]" multiple accept="image/*"><br><br>

            <label><input type="checkbox" name="disponible" checked> Hay Stock (Disponible en cocina)</label><br>
            <label><input type="checkbox" name="ofertado" checked> Activo en la Carta (Ofertado)</label>
        </fieldset>
        
        <br>
        <button type="submit" class="boton">Guardar Producto</button>
        <a href="<?php echo RUTA_APP; ?>/admin_productos.php">Cancelar</a>
    </form>
</div>