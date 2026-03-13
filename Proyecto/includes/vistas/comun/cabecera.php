<?php
/**
 * Crea una cadena personalizada cuando hay un usuario con sesión iniciada, que se muestra en la cabecera junto al logo (que funciona como botón de inicio)
 *
 * @return string
 */
function mostrarSaludo() {
    if (isset($_SESSION['login']) && ($_SESSION['login']===true)) {
        //Añadimos el enlace a perfil.php justo antes del de salir
        return "Bienvenido, {$_SESSION['nombre']} 
                <a href='" . RUTA_APP . "/perfil.php'>(mi perfil)</a> 
                <a href='" . RUTA_APP . "/logout.php'>(salir)</a>";
    } else {
        return "Usuario desconocido. <a href='" . RUTA_APP . "/login.php'>Login</a> <a href='" . RUTA_APP . "/registro.php'>Registro</a>";
    }
}
?>
<header>
    <a href="<?= RUTA_APP ?>/index.php" id="logo-enlace">
        <img src="<?= RUTA_IMGS ?>/Logo.png" alt="Ir al inicio" id="logo-cabecera" height="100">
    </a>
    
    <div class="saludo"><?= mostrarSaludo(); ?></div>
</header>
