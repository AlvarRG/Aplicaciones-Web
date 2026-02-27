<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioEditarProducto;
use es\ucm\fdi\aw\Aplicacion;

// 1. Seguridad: Solo el Gerente
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    header('Location: index.php');
    exit();
}

$conn = Aplicacion::getInstance()->getConexionBd();
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    header('Location: admin_productos.php');
    exit();
}

// 2. Obtener datos del producto
$query = "SELECT * FROM Productos WHERE id = $id";
$rs = $conn->query($query);
$product = $rs->fetch_assoc();

if (!$product) {
    die("Producto no encontrado.");
}

$tituloPagina = "Editar Producto: " . htmlspecialchars($product['nombre']);

// 3. Instanciar el formulario
$form = new FormularioEditarProducto($id);
$htmlFormulario = $form->gestiona();

// 4. Montar el contenido de la vista
$contenidoPrincipal = <<<EOS
    <h1>Editar Producto: {$product['nombre']}</h1>
    <p><a href="admin_productos.php">⬅ Volver al listado</a></p>
    $htmlFormulario
    <script src="js/productos.js"></script>
EOS;

require 'includes/vistas/plantillas/plantilla.php';