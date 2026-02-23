<?php
require_once __DIR__.'/utils.php';
session_start();

if (!isset($_POST['actualizar'])) {
    header('Location: perfil.php');
    exit();
}

$conn = conexionBD();
$nombreUsuario = $_SESSION['nombreUsuario'];

// 1. Recoger textos
$nombre = $conn->real_escape_string($_POST['nombre']);
$apellidos = $conn->real_escape_string($_POST['apellidos']);
$email = $conn->real_escape_string($_POST['email']);
$avatarFinal = $_POST['avatar_pre'];

// 2. Lógica de Avatar
if (isset($_POST['borrar_foto'])) {
    $avatarFinal = 'default.png';
} 
// Si ha subido un archivo
else if ($_FILES['nueva_foto']['name'] != "") {
    $directorio = "img/avatares/";
    $extension = pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION);
    $nombreArchivo = $nombreUsuario . "_" . time() . "." . $extension;
    $rutaDestino = $directorio . $nombreArchivo;

    if (move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $rutaDestino)) {
        $avatarFinal = $nombreArchivo;
    }
}

// 3. Actualizar BD
$query = "UPDATE Usuarios SET nombre='$nombre', apellidos='$apellidos', email='$email', avatar='$avatarFinal' WHERE nombreUsuario='$nombreUsuario'";

if ($conn->query($query)) {
    $_SESSION['nombre'] = $nombre;
    header('Location: perfil.php?success=1');
} else {
    echo "Error al actualizar: " . $conn->error;
}