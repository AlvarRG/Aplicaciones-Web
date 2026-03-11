<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\FormularioEditarUsuario;

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    //Obtenemos el nombre del usuario a editar. Este dato, solo lo usaremos para montar el contenido principal de la página
    $usuarioObj = Usuario::buscaPorId((int)$id);
    $usuario = ['nombreUsuario' => $usuarioObj ? $usuarioObj->getNombreUsuario() : ''];

    //Creamos el formulario de edición
    $form = new FormularioEditarUsuario($id);
    $htmlFormEditarUsuario = $form->gestiona();

    //Parametros para la plantilla
    $tituloPagina = "Editar Usuario";

    $contenidoPrincipal = <<<EOS
        <h1>Editar Rol de: {$usuario['nombreUsuario']}</h1>
        <p><a href="admin_usuarios.php">⬅ Volver al listado</a></p>
        $htmlFormEditarUsuario
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';