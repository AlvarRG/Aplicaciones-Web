<?php
// Inicio del procesamiento
session_start();

$tituloPagina = 'Login';

$contenidoPrincipal = <<<EOS
<h1>Acceso al sistema</h1>
<form action="procesarLogin.php" method="POST">
    <fieldset>
        <legend>Usuario y contraseña</legend>
        <div>
            <label for="nombreUsuario">Nombre de usuario:</label>
            <input id="nombreUsuario" type="text" name="nombreUsuario" />
        </div>
        <div>
            <label for="password">Password:</label>
            <input id="password" type="password" name="password" />
        </div>
        <div>
            <button type="submit" name="login">Entrar</button>
        </div>
    </fieldset>
</form>
EOS;

require 'includes/vistas/plantillas/plantilla.php';
?>