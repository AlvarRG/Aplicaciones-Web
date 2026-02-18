<div class="formulario-categoria">
    <h1>Crear / Editar Categoría</h1>
    
    <form action="#" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Datos de la Categoría</legend>
            
            <label>Nombre de la Categoría:</label><br>
            <input type="text" name="nombre" required size="40"><br><br>
            
            <label>Descripción:</label><br>
            <textarea name="descripcion" rows="4" cols="40"></textarea><br><br>
            
            <label>Imagen Representativa (Opcional):</label><br>
            <input type="file" name="imagen" accept="image/*"><br>
            <small>Sube una imagen pequeña para ilustrar la categoría en el menú.</small>
        </fieldset>
        
        <br>
        <button type="submit" class="boton">Guardar Categoría</button>
        <a href="<?php echo RUTA_APP; ?>/admin_categorias.php">Cancelar</a>
    </form>
</div>