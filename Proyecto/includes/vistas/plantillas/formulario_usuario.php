<div class="formulario-usuario">
    <h1>Gestión de Empleados y Usuarios</h1>
    <p>Utiliza este formulario para dar de alta a un nuevo empleado o modificar el rol de un usuario existente.</p>
    
    <form action="#" method="POST">
        <fieldset>
            <legend>Datos Personales y Acceso</legend>
            
            <label>Nombre Completo:</label><br>
            <input type="text" name="nombre" required size="40" placeholder="Ej: Carlos López"><br><br>
            
            <label>Correo Electrónico:</label><br>
            <input type="email" name="email" required size="40" placeholder="carlos@ejemplo.com"><br><br>
            
            <label>Nombre de Usuario (Login):</label><br>
            <input type="text" name="username" required size="30"><br><br>
            
            <label>Contraseña:</label><br>
            <input type="password" name="password" size="30"><br>
            <small>(Déjalo en blanco si estás editando y no quieres cambiarla)</small>
        </fieldset>

        <fieldset>
            <legend>Asignación de Rol</legend>
            <label>Selecciona el rol del usuario en el sistema:</label><br>
            <select name="rol" required style="padding: 5px; font-size: 1.1em;">
                <option value="cliente">Cliente (Usuario estándar)</option>
                <option value="camarero">Camarero (Acceso a TPV)</option>
                <option value="cocinero">Cocinero (Acceso a Comandas)</option>
                <option value="gerente">Gerente (Administrador total)</option>
            </select>
        </fieldset>
        
        <br>
        <button type="submit" class="boton">Guardar Usuario</button>
        <a href="<?php echo RUTA_APP; ?>/admin_usuarios.php" class="boton-secundario">Cancelar</a>
    </form>
</div>