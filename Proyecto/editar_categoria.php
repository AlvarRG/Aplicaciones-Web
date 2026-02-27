<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\FormularioEditarCategoria;
use es\ucm\fdi\aw\Aplicacion;

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) exit();

$conn = Aplicacion::getInstance()->getConexionBd();
$id = $_GET['id'];
$res = $conn->query("SELECT * FROM Categorias WHERE id = $id");
$cat = $res->fetch_assoc();

$form = new FormularioEditarCategoria($id);
$htmlFormulario = $form->gestiona();

$tituloPagina = "Editar Categoría";
$contenidoPrincipal = <<<EOS
    <h1>Editar Categoría: {$cat['nombre']}</h1>
    $htmlFormulario
EOS;
require 'includes/vistas/plantillas/plantilla.php';

