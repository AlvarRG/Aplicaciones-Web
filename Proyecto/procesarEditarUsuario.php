<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nuevoRol = $_POST['nuevo_rol'];
    $conn = conexionBD();

    // En este sistema simple, un usuario tiene un solo rol principal.
    // Borramos el anterior y asignamos el nuevo.
    $conn->query("DELETE FROM RolesUsuario WHERE usuario = $id");
    $conn->query("INSERT INTO RolesUsuario (usuario, rol) VALUES ($id, $nuevoRol)");

    header('Location: admin_usuarios.php');
    exit();
}