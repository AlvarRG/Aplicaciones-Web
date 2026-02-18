<?php
// Leemos la sesión real
$estaLogueado = isset($_SESSION['login']) && $_SESSION['login'] === true;
$esCamarero   = $estaLogueado && isset($_SESSION['rol']) && $_SESSION['rol'] === 'camarero';
$esGerente    = $estaLogueado && isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente';
$esCocinero   = $estaLogueado && isset($_SESSION['rol']) && $_SESSION['rol'] === 'cocinero';
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

    <?php if ($esCamarero || $esGerente): ?>
        <hr>
        <h3>Zona Camareros</h3>
        <ul>
            <li>
                <a href="<?php echo RUTA_APP; ?>/panel_camarero.php" style="color: #0066cc;">
                    <strong>Terminal TPV</strong>
                </a>
            </li>
        </ul>
    <?php endif; ?>

    <?php if ($esCocinero || $esGerente): ?>
        <hr>
        <h3>Zona Cocina</h3>
        <ul>
            <li>
                <a href="#" style="color: #e67e22;">
                    <strong>Panel de Cocina</strong>
                </a>
            </li>
        </ul>
    <?php endif; ?>

    <?php if ($esGerente): ?>
        <hr>
        <h3>Administración</h3>
        <ul>
            <li><a href="<?php echo RUTA_APP; ?>/admin_pedidos.php" style="color: #d32f2f;">Todos los Pedidos</a></li>
            <li><a href="<?php echo RUTA_APP; ?>/admin_productos.php">Productos y Categorías</a></li>
            <li><a href="<?php echo RUTA_APP; ?>/admin_usuarios.php">Gestión de Empleados</a></li>
        </ul>
    <?php endif; ?>
</nav>