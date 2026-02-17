<div class="formulario-perfil">
    <h1>Editar mis datos</h1>
    
    <form action="<?php echo RUTA_APP; ?>/procesar_perfil.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Información Personal</legend>
            <label>Nombre:</label>
            <input type="text" name="nombre" value="Juan Pérez"><br>
            
            <label>Email:</label>
            <input type="email" name="email" value="juan@ejemplo.com"><br>
        </fieldset>

        <fieldset>
            <legend>Cambiar Contraseña</legend>
            <p class="nota">Deja esto en blanco si no quieres cambiarla.</p>
            <label>Nueva Contraseña:</label>
            <input type="password" name="password"><br>
        </fieldset>

        <fieldset>
            <legend>Gestión de Avatar</legend>
            
            <label>Subir foto nueva:</label>
            <input type="file" name="avatar_fichero" accept="image/*"><br>
            
            <label>O elegir uno predefinido:</label>
            <select name="avatar_predefinido">
                <option value="none">-- Mantener actual --</option>
                <option value="avatar1.png">Avatar Chico</option>
                <option value="avatar2.png">Avatar Chica</option>
                <option value="avatar3.png">Chef</option>
            </select>

            <br><br>
            <label><input type="checkbox" name="eliminar_avatar"> Eliminar mi foto actual (usar por defecto)</label>
        </fieldset>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>