<?php
require_once __DIR__.'/utils.php';
session_start();

// 1. Seguridad: Solo el Gerente puede procesar
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    exit("Acceso denegado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = conexionBD();

    // 2. Recogida de datos y saneamiento
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $id_categoria = (int)$_POST['id_categoria'];
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $precio_base = (float)$_POST['precio_base'];
    $iva = (int)$_POST['iva'];
    
    // Checkbox: si no se marca, no llega en el POST
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $ofertado = 1; // Por defecto al crearlo está en la carta

    // 3. Gestión de la Imagen
    $imagen = 'prod_default.png';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $dir = "img/productos/";
        // Creamos la carpeta si no existe (por seguridad)
        if (!file_exists($dir)) { mkdir($dir, 0777, true); }
        
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreImg = "prod_" . time() . "." . $ext;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
            $imagen = $nombreImg;
        }
    }

    // 4. Inserción en la BD
    $query = "INSERT INTO Productos (id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado, imagen) 
              VALUES ($id_categoria, '$nombre', '$descripcion', $precio_base, $iva, $disponible, $ofertado, '$imagen')";

    if ($conn->query($query)) {
        // Si todo va bien, volvemos al listado de productos
        header('Location: admin_productos.php?success=1');
        exit();
    } else {
        echo "Error al crear el producto: " . $conn->error;
    }
}