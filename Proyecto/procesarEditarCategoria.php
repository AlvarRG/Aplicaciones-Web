<?php
require_once __DIR__.'/utils.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: admin_categorias.php');
    exit();
}

$conn = conexionBD();
$id = (int)$_POST['id'];
$nombre = $conn->real_escape_string($_POST['nombre']);
$descripcion = $conn->real_escape_string($_POST['descripcion']);

// Recuperar imagen actual por si no se cambia
$res = $conn->query("SELECT imagen FROM Categorias WHERE id = $id");
$fila = $res->fetch_assoc();
$imagenFinal = $fila['imagen'];

// Si se sube una nueva imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $dir = "img/categorias/";
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombreImg = "cat_" . $id . "_" . time() . "." . $ext;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
        $imagenFinal = $nombreImg;
    }
}

$query = "UPDATE Categorias SET nombre='$nombre', descripcion='$descripcion', imagen='$imagenFinal' WHERE id = $id";

if ($conn->query($query)) {
    header('Location: admin_categorias.php?success=edit');
} else {
    echo "Error al actualizar categoría: " . $conn->error;
}