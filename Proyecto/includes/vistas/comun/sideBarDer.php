<?php
// --- CONFIGURACIÓN DE SIMULACIÓN (Para probar las vistas sin BD) ---
// Cambia esto a 'true' o 'false' para ver cómo cambia la barra lateral
$estaLogueado = false;  // ¿El usuario ha hecho login?
$esGerente = false;     // ¿El usuario es Gerente (Admin)?
// ------------------------------------------------------------------
?>

<aside id="sidebar-right">

    <?php if ($estaLogueado): ?>
        <div class="usuario-panel">
            <h3>Mi Cuenta</h3>
            <div class="avatar">
                <img src="<?php echo RUTA_IMGS; ?>usuario_ejemplo.png" alt="Avatar" width="50">
            </div>
            <p>Hola, <strong>Usuario Demo</strong></p>
            
            <ul class="menu-usuario">
                <li><a href="<?php echo RUTA_APP; ?>/perfil.php">Ver mi Perfil</a></li>
                
                <li><a href="<?php echo RUTA_APP; ?>/editar_perfil.php">Editar mis datos</a></li>
                
                <?php if ($esGerente): ?>
                    <li><a href="<?php echo RUTA_APP; ?>/admin_usuarios.php" style="color: red;"><strong>Gestionar Usuarios</strong></a></li>
                <?php endif; ?>

                <li><a href="<?php echo RUTA_APP; ?>/mis_pedidos.php">Mis Pedidos</a></li>
                <li><a href="<?php echo RUTA_APP; ?>/logout.php">Cerrar Sesión</a></li>
            </ul>
        </div>

    <?php else: ?>
        <div class="login-panel">
            <h3>Identifícate</h3>
            <form action="<?php echo RUTA_APP; ?>/login.php" method="POST">
                <fieldset>
                    <div>
                        <label>Usuario:</label><br>
                        <input type="text" name="username" size="10" />
                    </div>
                    <div>
                        <label>Contraseña:</label><br>
                        <input type="password" name="password" size="10" />
                    </div>
                    <div>
                        <button type="submit">Entrar</button>
                    </div>
                </fieldset>
            </form>
            <p>
                <a href="<?php echo RUTA_APP; ?>/registro.php">¿No tienes cuenta? Regístrate</a>
            </p>
        </div>
    <?php endif; ?>

    <div class="carrito-panel">
        <h3>Tu Pedido</h3>
        <p>0 productos</p>
        <a href="<?php echo RUTA_APP; ?>/carrito.php">Ver Carrito</a>
    </div>

</aside>