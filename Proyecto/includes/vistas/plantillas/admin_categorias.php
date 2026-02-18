<div class="gestion-categorias">
    <h1>Gestión de Categorías</h1>
    
    <div class="acciones-superiores">
        <a href="<?php echo RUTA_APP; ?>/crear_categoria.php" class="boton">+ Añadir Nueva Categoría</a>
        <a href="<?php echo RUTA_APP; ?>/admin_productos.php" class="boton-secundario">Volver a Productos</a>
    </div>
    <br>

    <table border="1" width="100%" cellpadding="8" style="border-collapse: collapse; text-align: left;">
        <thead style="background-color: #f2f2f2;">
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img src="<?php echo RUTA_IMGS; ?>cat_entrantes.jpg" width="50" alt="Entrantes"></td>
                <td>Entrantes</td>
                <td>Para ir abriendo el apetito. Raciones para compartir.</td>
                <td>
                    <a href="<?php echo RUTA_APP; ?>/editar_categoria.php?id=1">Editar</a> | 
                    <a href="#" style="color: red;">Borrar</a>
                </td>
            </tr>
            <tr>
                <td><img src="<?php echo RUTA_IMGS; ?>cat_bebidas.jpg" width="50" alt="Bebidas"></td>
                <td>Bebidas</td>
                <td>Refrescos, cervezas, vinos y agua.</td>
                <td>
                    <a href="<?php echo RUTA_APP; ?>/editar_categoria.php?id=2">Editar</a> | 
                    <a href="#" style="color: red;">Borrar</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>