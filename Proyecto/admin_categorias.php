<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

// Seguridad: Solo el Gerente
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: index.php');
    exit();
}

$tituloPagina = 'Gestión de Categorías';

$conn = Aplicacion::getInstance()->getConexionBd();
$query = "SELECT * FROM Categorias";
$rs = $conn->query($query);

$filas = "";
while ($fila = $rs->fetch_assoc()) {
    $id = $fila['id'];
    $img = $fila['imagen'];
    $filas .= <<<EOS
        <tr>
            <td><img src="img/categorias/$img" width="50"></td>
            <td>{$fila['nombre']}</td>
            <td>{$fila['descripcion']}</td>
            <td>
                <a href="editar_categoria.php?id=$id">[Editar]</a>
                <a href="borrar_categoria.php?id=$id" style="color:red;" 
				   onclick="return confirm('¡OJO! Esto borrará la categoría de la base de datos permanentemente. ¿Proceder?')">
				   [Borrar]</a>
            </td>
        </tr>
EOS;
}

$contenidoPrincipal = <<<EOS
    <h1>Categorías de Productos</h1>
    <p><a href="nueva_categoria.php"> Crear Nueva Categoría</a></p>
    <table border="1">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>$filas</tbody>
    </table>
EOS;

require 'includes/vistas/plantillas/plantilla.php';