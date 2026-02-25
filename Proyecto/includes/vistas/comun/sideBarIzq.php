<nav id="sidebarIzq">
    <h3>Navegacion</h3>
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
            <li><a href="perfil.php">Mi Perfil</a></li>
            <li><a href="mis_pedidos.php">Mis Pedidos</a></li>
        <?php endif; ?>
        <li><a href="carta.php">Ver la carta</a></li>
    </ul>
    
    <?php 
        $esAdmin = isset($_SESSION['esAdmin']) ? $_SESSION['esAdmin'] : false;
        $esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;
        $esCocinero = isset($_SESSION['esCocinero']) ? $_SESSION['esCocinero'] : false;
        
        $esPersonal = $esAdmin || $esCamarero || $esCocinero;
    ?>
    
    <?php if ($esPersonal): ?>
        <h3>Gestion de pedidos</h3>
        <ul>
            <li><a href="gestion_pedidos.php">Pedidos (Global)</a></li>
            
            <?php if ($esCamarero): ?>
                <li><a href="tablet_camarero.php">Tablet Camarero</a></li>
            <?php endif; ?>

            <?php if ($esCocinero): ?>
                <li><a href="tablet_cocina.php">Tablet Cocina</a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

    <?php if ($esAdmin): ?>
        <h3>Administracion</h3>
        <ul>
            <li><a href="admin_usuarios.php">Usuarios</a></li>
            <li><a href="admin_categorias.php">Categorias</a></li>
            <li><a href="admin_productos.php">Productos (Carta)</a></li>
        </ul>
    <?php endif; ?>
</nav>