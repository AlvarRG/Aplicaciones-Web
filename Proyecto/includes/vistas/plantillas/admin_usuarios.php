<div class="gestion-usuarios">
    <h1>Gestión de Usuarios (Zona Gerente)</h1>
    
    <div class="filtros">
        Buscar usuario: <input type="text" placeholder="Nombre...">
        <button>Filtrar</button>
    </div>
    <br>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>juanperez</td>
                <td>Juan Pérez</td>
                <td>Cliente</td>
                <td>
                    <a href="#">Editar Rol</a> | <a href="#" style="color:red">Borrar</a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>chef_antonio</td>
                <td>Antonio García</td>
                <td>Cocinero</td>
                <td>
                    <a href="#">Editar Rol</a> | <a href="#" style="color:red">Borrar</a>
                </td>
            </tr>
        </tbody>
    </table>
    
    <br>
    <a href="<?php echo RUTA_APP; ?>/registro.php" class="boton">Añadir Nuevo Empleado</a>
</div>