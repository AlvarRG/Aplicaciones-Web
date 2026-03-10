<?php
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\FormularioEditarCategoria;

require_once __DIR__.'/includes/config.php';

// Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    // Obtenemos el nombre de la categoría a editar. Este dato, solo lo usaremos para montar el contenido principal de la página
    $conn = Aplicacion::getInstance()->getConexionBd();
    $query = "SELECT * FROM Categorias WHERE id = $id";
    $rs = $conn->query($query);
    if ($rs && $rs->num_rows > 0) {
        $cat = $rs->fetch_assoc();
    }

    // Creamos el formulario de edición
    $form = new FormularioEditarCategoria($id);
    $htmlFormEditarCategoria = $form->gestiona();

    // Parametros para la plantilla
    $tituloPagina = "Editar Categoría";

    $contenidoPrincipal = <<<EOS
        <h1>Editar Categoría: {$cat['nombre']}</h1>
        $htmlFormEditarCategoria
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';

