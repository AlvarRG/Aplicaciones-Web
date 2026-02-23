<?php
require_once __DIR__.'/utils.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: admin_productos.php');
    exit();
}

$conn = conexionBD();
$id = (int)$_POST['id'];
$nombre = $conn->real_escape_string($_POST['nombre']);
$id_categoria = (int)$_POST['id_categoria'];
$precio_base = (float)$_POST['precio_base'];
$iva = (int)$_POST['iva'];
$disponible = isset($_POST['disponible']) ? 1 : 0;
$ofertado = isset($_POST['ofertado']) ? 1 : 0;

// Recuperar imagen actual
$res = $conn->query("SELECT imagen FROM Productos WHERE id = $id");
$fila = $res->fetch_assoc();
$imagenFinal = $fila['imagen'];

// Gestión de nueva imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $dir = "img/productos/";
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombreImg = "prod_" . $id . "_" . time() . "." . $ext;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
        $imagenFinal = $nombreImg;
    }
}

$query = "UPDATE Productos SET 
            nombre='$nombre', 
            id_categoria=$id_categoria, 
            precio_base=$precio_base, 
            iva=$iva, 
            disponible=$disponible, 
            ofertado=$ofertado, 
            imagen='$imagenFinal' 
          WHERE id = $id";

if ($conn->query($query)) {
    header('Location: admin_productos.php?success=edit');
} else {
    echo "Error al actualizar producto: " . $conn->error;
}