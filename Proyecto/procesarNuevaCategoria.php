<?php
require_once __DIR__.'/utils.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$_SESSION['esAdmin']) {
    header('Location: admin_categorias.php');
    exit();
}

$conn = conexionBD();
$nombre = $conn->real_escape_string($_POST['nombre']);
$descripcion = $conn->real_escape_string($_POST['descripcion']);
$imagen = 'cat_default.png';

// Lógica de subida de imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $dir = "img/categorias/";
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombreImg = "cat_" . time() . "." . $ext;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
        $imagen = $nombreImg;
    }
}

$query = "INSERT INTO Categorias (nombre, descripcion, imagen) VALUES ('$nombre', '$descripcion', '$imagen')";

if ($conn->query($query)) {
    header('Location: admin_categorias.php');
} else {
    echo "Error al crear categoría: " . $conn->error;
}