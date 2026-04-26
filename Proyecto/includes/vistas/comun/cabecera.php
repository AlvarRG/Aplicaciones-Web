<?php $rutaApp = RUTA_APP; ?>

<header>
    <a href="<?= $rutaApp ?>/index.php" class="header-logo">
        <img src="<?= RUTA_IMGS ?>/logo.png" alt="Bistro FDI" height="80">
    </a>

    <div class="header-usuario">
        <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
            <span>Hola, <?= $_SESSION['nombre'] ?></span>
            <a href="<?= $rutaApp ?>/perfil.php">Mi perfil</a>
            <a href="<?= $rutaApp ?>/logout.php">Salir</a>
        <?php else: ?>
            <a href="<?= $rutaApp ?>/login.php">Login</a>
            <a href="<?= $rutaApp ?>/registro.php">Registro</a>
        <?php endif; ?>
    </div>
</header>
