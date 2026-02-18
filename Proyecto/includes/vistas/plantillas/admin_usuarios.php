<div class="acciones-superiores">
    <a href="<?php echo RUTA_APP; ?>/crear_usuario.php" class="boton">+ Añadir Nuevo Empleado</a>
</div>
<br>
<table border="1" width="100%" cellpadding="8" style="border-collapse: collapse;">
    <thead style="background-color: #f2f2f2;">
        <tr>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Rol Actual</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>user_carlos</td>
            <td>Carlos López</td>
            <td><strong>Camarero</strong></td>
            <td>
                <a href="<?php echo RUTA_APP; ?>/editar_usuario.php?id=2">Editar Rol</a> | 
                <a href="#" style="color: red;">Eliminar</a>
            </td>
        </tr>
    </tbody>
</table>