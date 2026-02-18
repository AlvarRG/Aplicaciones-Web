<?php
// --- LECTURA DE SESIÓN REAL ---
// Ya no usamos 'true' o 'false' a mano, leemos la memoria del servidor
$estaLogueado = isset($_SESSION['login']) && $_SESSION['login'] === true;
$esGerente    = $estaLogueado && isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente';

// Si está logueado, cogemos su nombre, si no, lo dejamos por defecto
$nombreUsuario = $estaLogueado ? $_SESSION['nombre_usuario'] : 'Invitado';
// ------------------------------------------------------------------
?>

<aside id="sidebar-right">

    <?php if ($estaLogueado): ?>
        <div class="usuario-panel">
            <h3>Mi Cuenta</h3>
            <div class="avatar">
                <img src="<?php echo RUTA_IMGS; ?>usuario_ejemplo.png" alt="Avatar" width="50">
            </div>
            <p>Hola, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong></p>
            
            <ul class="menu-usuario">
                <li><a href="<?php echo RUTA_APP; ?>/perfil.php">Ver mi Perfil</a></li>
                
                <li><a href="<?php echo RUTA_APP; ?>/editar_perfil.php">Editar mis datos</a></li>
                
                <?php if ($esGerente): ?>
                    <li><hr></li> <li><span style="color: gray; font-size: 0.9em;">Panel Gerente</span></li>
                    <li><a href="<?php echo RUTA_APP; ?>/admin_usuarios.php" style="color: red;">Gestión de Usuarios</a></li>
                    <li><a href="<?php echo RUTA_APP; ?>/admin_productos.php" style="color: red;"><strong>Gestión de Carta/Productos</strong></a></li>
                    <li><a href="<?php echo RUTA_APP; ?>/admin_categorias.php" style="color: red;">Gestión de Categorías</a></li>
                    <li><hr></li>
                <?php endif; ?>

                <li><a href="<?php echo RUTA_APP; ?>/mis_pedidos.php">Mis Pedidos</a></li>
                <li><a href="<?php echo RUTA_APP; ?>/logout.php">Cerrar Sesión</a></li>
            </ul>
        </div>

    <?php else: ?>
        <div class="login-panel">
            <h3>Identifícate</h3>
            <form action="<?php echo RUTA_APP; ?>/procesar_login.php" method="POST">
                <fieldset>
                    <div>
                        <label>Usuario:</label><br>
                        <input type="text" name="username" size="10" required />
                    </div>
                    <div>
                        <label>Contraseña:</label><br>
                        <input type="password" name="password" size="10" required />
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