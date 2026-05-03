<?php
use es\ucm\fdi\aw\usuarios\Usuario;

require_once __DIR__.'/includes/config.php';

function renderFilaUsuario($fila, $rutaApp, $rutaImgs) {
    $nombreUsuario = $fila->getNombreUsuario();
    $nombre = $fila->getNombre();
    $apellidos = $fila->getApellidos();
    $email = $fila->getEmail();
    $nombreRol = $fila->getNombreRol();
    $id = $fila->getId();

    return <<<HTML
        <tr>
            <td>{$nombreUsuario}</td>
            <td>{$nombre} {$apellidos}</td>
            <td>{$email}</td>
            <td>{$nombreRol}</td>
            <td class="admin-usuarios-acciones">
                <a href="$rutaApp/editar_usuario.php?id={$id}"><img src="{$rutaImgs}/edit.png" width="30" alt="Editar"></a> 
                <a href="$rutaApp/includes/borrar_usuario.php?id={$id}" class="admin-usuarios-eliminar boton-borrar" data-mensaje="Esto borrará al usuario de la base de datos permanentemente. ¿Proceder?"><img src="{$rutaImgs}/borrar.png" width="30" alt="Borrar"></a>
            </td>
        </tr>
HTML;
}

//Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    //Obtener todos los usuarios haciendo uso de la función buscaTodos() de la clase Usuario
    $usuarios = Usuario::buscaTodos();
    
    $rutaApp = RUTA_APP;
    $rutaJs = RUTA_JS;
    $rutaImgs = RUTA_IMGS;

    //Si la consulta anterior ha devuelto algo, recorremos los usuarios devueltos y construimos las filas de la tabla
    $filas = "";
    if(!empty($usuarios)) {
        foreach ($usuarios as $fila) {
            $filas .= renderFilaUsuario($fila, $rutaApp, $rutaImgs);
        }
    }

    //Parametros para la plantilla
    $estilosExtra = ['admin_usuarios.css'];

    $tituloPagina = 'Gestión de Usuarios';
    $contenidoPrincipal = <<<EOS
        <h1>Panel de Administración</h1>
        <table class="admin-usuarios-tabla">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                $filas
            </tbody>
        </table>
        <script src="$rutaJs/confirmacion_borrado.js"></script>
EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';