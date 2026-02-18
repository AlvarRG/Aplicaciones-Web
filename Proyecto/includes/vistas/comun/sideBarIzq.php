<?php
// --- CONFIGURACIÓN DE SIMULACIÓN DE ROLES ---
// Cambia estos valores a 'true' o 'false' para probar la interfaz
$estaLogueado = true;  // ¿Hay un usuario conectado?
$esCamarero   = false; // ¿El usuario es un Camarero?
$esGerente    = true;  // ¿El usuario es el Gerente?
// --------------------------------------------
?>

<nav id="sidebar-left" class="menu-lateral">
    <h3>Bistro FDI</h3>
    <ul>
        <li><a href="<?php echo RUTA_APP; ?>/index.php">Inicio</a></li>
        <li><a href="<?php echo RUTA_APP; ?>/carta.php">Nuestra Carta</a></li>
    </ul>

    <?php if ($estaLogueado): ?>
        <hr>
        <h3>Mi Compra</h3>
        <ul>
            <li><a href="<?php echo RUTA_APP; ?>/carrito.php">Ver mi Carrito</a></li>
            <li><a href="<?php echo RUTA_APP; ?>/mis_pedidos.php">Estado de mis Pedidos</a></li>
        </ul>
    <?php endif; ?>

    <?php if ($esCamarero): ?>
        <hr>
        <h3>Zona Empleados</h3>
        <ul>
            <li>
                <a href="<?php echo RUTA_APP; ?>/panel_camarero.php" style="color: #0066cc;">
                    <strong>Terminal TPV (Camareros)</strong>
                </a>
            </li>
        </ul>
    <?php endif; ?>

    <?php if ($esGerente): ?>
        <hr>
        <h3>Monitorización</h3>
        <ul>
            <li>
                <a href="<?php echo RUTA_APP; ?>/admin_pedidos.php" style="color: #d32f2f;">
                    <strong>Todos los Pedidos (Global)</strong>
                </a>
            </li>
        </ul>
    <?php endif; ?>
</nav>