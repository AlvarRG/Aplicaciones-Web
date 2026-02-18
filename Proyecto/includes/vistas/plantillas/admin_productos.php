<div class="gestion-productos">
    <h1>Gestión de Productos</h1>
    
    <div class="acciones-superiores">
        <a href="<?php echo RUTA_APP; ?>/crear_producto.php" class="boton">+ Añadir Nuevo Producto</a>
        <a href="<?php echo RUTA_APP; ?>/admin_categorias.php" class="boton-secundario">Gestionar Categorías</a>
    </div>
    <br>

    <table border="1" width="100%" cellpadding="8" style="border-collapse: collapse; text-align: left;">
        <thead style="background-color: #f2f2f2;">
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio Base</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img src="<?php echo RUTA_IMGS; ?>hamburguesa.jpg" width="50" alt="Burger"></td>
                <td>Hamburguesa Bistro</td>
                <td>Platos Principales</td>
                <td>10.00 €</td>
                <td style="color: green;">Ofertado / Disponible</td>
                <td>
                    <a href="<?php echo RUTA_APP; ?>/editar_producto.php?id=1">Editar</a> | 
                    <a href="#" style="color: red;" title="Borrado lógico">Retirar</a>
                </td>
            </tr>
            <tr>
                <td><img src="<?php echo RUTA_IMGS; ?>refresco.jpg" width="50" alt="Cola"></td>
                <td>Refresco de Cola</td>
                <td>Bebidas</td>
                <td>2.00 €</td>
                <td style="color: orange;">Ofertado / Sin Stock</td>
                <td>
                    <a href="<?php echo RUTA_APP; ?>/editar_producto.php?id=2">Editar</a> | 
                    <a href="#" style="color: red;">Retirar</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>