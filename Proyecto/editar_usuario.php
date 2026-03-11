<?php
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\FormularioEditarUsuario;

require_once __DIR__.'/includes/config.php';

// Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    // Obtenemos el nombre del usuario a editar. Este dato, solo lo usaremos para montar el contenido principal de la página
    $queryNombreUsuarioPorId = "SELECT nombreUsuario FROM usuarios WHERE id = ?";
    $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryNombreUsuarioPorId, "i", (int)$id)->get_result();
    if ($rs && $rs->num_rows > 0) {
        $usuario = $rs->fetch_assoc();
    }
    if ($rs) {
        $rs->free();
    }

    // Creamos el formulario de edición
    $form = new FormularioEditarUsuario($id);
    $htmlFormEditarUsuario = $form->gestiona();

    // Parametros para la plantilla
    $tituloPagina = "Editar Usuario";

    $contenidoPrincipal = <<<EOS
        <h1>Editar Rol de: {$usuario['nombreUsuario']}</h1>
        <p><a href="admin_usuarios.php">⬅ Volver al listado</a></p>
        $htmlFormEditarUsuario
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';