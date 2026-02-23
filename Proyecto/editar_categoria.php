<?php
require_once __DIR__.'/utils.php';
session_start();
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$conn = conexionBD();
$id = $_GET['id'];
$res = $conn->query("SELECT * FROM Categorias WHERE id = $id");
$cat = $res->fetch_assoc();

$tituloPagina = "Editar Categoría";
$contenidoPrincipal = <<<EOS
    <h1>Editar Categoría: {$cat['nombre']}</h1>
    <form action="procesarEditarCategoria.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="$id">
        <p>Nombre: <input type="text" name="nombre" value="{$cat['nombre']}" required></p>
        <p>Descripción:<br><textarea name="descripcion">{$cat['descripcion']}</textarea></p>
        <p>Imagen actual: <img src="img/categorias/{$cat['imagen']}" width="50"></p>
        <p>Cambiar imagen: <input type="file" name="imagen"></p>
        <button type="submit">Guardar Cambios</button>
    </form>
EOS;
require 'includes/vistas/plantillas/plantilla.php';