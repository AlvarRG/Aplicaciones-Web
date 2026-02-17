<div class="perfil-usuario">
    <h1>Mi Perfil</h1>
    
    <div class="datos-perfil">
        <div class="avatar-seccion">
            <img src="<?php echo RUTA_IMGS; ?>usuario_ejemplo.png" alt="Avatar" width="150"><br>
            <a href="<?php echo RUTA_APP; ?>/editar_perfil.php" class="boton-secundario">Cambiar Avatar</a>
        </div>

        <div class="info-personal">
            <p><strong>Nombre de Usuario:</strong> usuario_demo</p>
            <p><strong>Nombre Completo:</strong> Juan Pérez</p>
            <p><strong>Email:</strong> juan@ejemplo.com</p>
            <p><strong>Rol:</strong> Cliente</p>
            <p><strong>Fecha de registro:</strong> 12/02/2024</p>
        </div>
    </div>
    
    <div class="acciones-perfil">
        <a href="<?php echo RUTA_APP; ?>/editar_perfil.php" class="boton">Editar mis datos</a>
        <a href="<?php echo RUTA_APP; ?>/logout.php" class="boton-alerta">Cerrar Sesión</a>
    </div>
</div>