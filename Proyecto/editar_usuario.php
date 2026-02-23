<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    die("Acceso denegado.");
}

$conn = conexionBD();
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$res = $conn->query("SELECT nombreUsuario FROM Usuarios WHERE id = $id");
$usuario = $res->fetch_assoc();

$tituloPagina = 'Editar Rol';

$contenidoPrincipal = <<<EOS
    <h1>Editar Rol de: {$usuario['nombreUsuario']}</h1>
    <form action="procesarEditarUsuario.php" method="POST">
        <input type="hidden" name="id" value="$id">
        <label>Selecciona el nuevo rol:</label>
        <select name="nuevo_rol">
            <option value="1">Cliente (Básico)</option>
            <option value="2">Camarero</option>
            <option value="3">Cocinero</option>
            <option value="4">Gerente (Admin)</option>
        </select>
        <button type="submit">Guardar Cambios</button>
    </form>
EOS;

require 'includes/vistas/plantillas/plantilla.php';