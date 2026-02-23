<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    exit("No tienes permisos");
}

$tituloPagina = 'Nueva Categoría';

$contenidoPrincipal = <<<EOS
    <h1>Crear Categoría</h1>
    <form action="procesarNuevaCategoria.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Datos de la categoría</legend>
            <div>
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>
            <div>
                <label>Descripción:</label><br>
                <textarea name="descripcion" rows="4" cols="50"></textarea>
            </div>
            <div>
                <label>Imagen (Icono):</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
            <br>
            <button type="submit">Guardar Categoría</button>
        </fieldset>
    </form>
EOS;

require 'includes/vistas/plantillas/plantilla.php';