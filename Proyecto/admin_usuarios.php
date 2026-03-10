<?php
use es\ucm\fdi\aw\Usuario;

require_once __DIR__.'/includes/config.php';



// Comprobamos si el usuario es admin, si no lo es, bloqueamos este contenido y mostramos un mensaje de advertencia 
if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $tituloPagina = 'Acceso Denegado';
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    // Obtener todos los usuarios haciendo uso de la función buscaTodos() de la clase Usuario
    $usuarios = Usuario::buscaTodos();
    
    // Si la consulta anterior ha devuelto algo, recorremos los usuarios devueltos y construimos las filas de la tabla
    $filas = "";
    if(!empty($usuarios)) {
        foreach ($usuarios as $fila) {
            $filas .= <<<EOS
                <tr>
                    <td>{$fila['nombreUsuario']}</td>
                    <td>{$fila['nombre']} {$fila['apellidos']}</td>
                    <td>{$fila['email']}</td>
                    <td>{$fila['nombreRol']}</td>
                    <td>
                        <a href="editar_usuario.php?id={$fila['id']}">[Cambiar Rol]</a> 
                        <a href="includes/borrar_usuario.php?id={$fila['id']}" class="boton-borrar" data-mensaje="Esto borrará al usuario de la base de datos permanentemente. ¿Proceder?">[Borrar]</a>
                    </td>
                </tr>
            EOS;
        }
    }

    // Parametros para la plantilla
    $estilosExtra = ['admin_usuarios.css'];

    $tituloPagina = 'Gestión de Usuarios';

    $contenidoPrincipal = <<<EOS
        <h1>Panel de Administración</h1>
        <table>
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
        <script src="js/confirmacion_borrado.js"></script>
    EOS;
}

require __DIR__.'/includes/vistas/plantillas/plantilla.php';