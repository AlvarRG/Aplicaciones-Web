<nav id="sidebarIzq">
    <h3>Navegación</h3>
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <?php if (isset($_SESSION['login'])): ?>
            <li><a href="perfil.php">Mi Perfil</a></li>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['esAdmin']) && $_SESSION['esAdmin']): ?>
        <h3>Administración</h3>
        <ul>
            <li><a href="admin_usuarios.php">Usuarios</a></li>
            <li><a href="admin_categorias.php">Categorías</a></li>
            <li><a href="admin_productos.php">Productos (Carta)</a></li>
        </ul>
    <?php endif; ?>
</nav>