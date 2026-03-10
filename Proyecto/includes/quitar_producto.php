<?php
require_once __DIR__.'/config.php';
use es\ucm\fdi\aw\Aplicacion;

//Si no eres admin no hace nada
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: ../index.php'); exit();
}

//Si el id existe
if (isset($_GET['id'])) {
	//Conexión a la base de datos
    $conn = Aplicacion::getInstance()->getConexionBd();
	
	//Cogemos el valor tipo in del id
    $id = intval($_GET['id']);
    
    //En lugar de DELETE (lo borraría de la base de datos), usamos UPDATE para ocultarlo
    $query = "UPDATE Productos SET ofertado = 0, disponible = 0 WHERE id = $id";
    
	//Si la consulta tiene éxito redirigimos, si no mostrarmos el mensaje de error
    if ($conn->query($query)) {
        header('Location: ../admin_productos.php');
    } else {
        echo "Error al retirar el producto: " . $conn->error;
    }
}