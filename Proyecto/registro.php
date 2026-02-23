<?php
session_start();

$tituloPagina = 'Registro';

$contenidoPrincipal = <<<EOS
    <h1>Registro de usuario</h1>
    <form action="procesarRegistro.php" method="POST">
        <fieldset>
            <legend>Datos Personales</legend>
            <div>
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" required />
            </div>
            <div>
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" required />
            </div>
            <div>
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" required />
            </div>
        </fieldset>
        <br>
        <fieldset>
            <legend>Datos de Cuenta</legend>
            <div>
                <label for="nombreUsuario">Nombre de usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" required />
            </div>
            <div>
                <label for="password">Password:</label>
                <input id="password" type="password" name="password" required />
            </div>
            <div>
                <label for="password2">Reintroduce el password:</label>
                <input id="password2" type="password" name="password2" required />
            </div>
            <div>
                <button type="submit" name="registro">Registrar</button>
            </div>
        </fieldset>
    </form>
EOS;

require 'includes/vistas/plantillas/plantilla.php';