<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Gestión de Usuarios';

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $conn = Aplicacion::getInstance()->getConexionBd();
    $query = "SELECT U.id, U.nombreUsuario, U.nombre, U.apellidos, U.email, R.nombre AS nombreRol 
              FROM usuarios U 
              JOIN roles R ON U.rol = R.id";
    $rs = $conn->query($query);
    
    $filas = "";
    while ($fila = $rs->fetch_assoc()) {
        $idU = $fila['id'];
        $nombreRol = $fila['nombreRol'];

        $filas .= <<<EOS
            <tr>
                <td>{$fila['nombreUsuario']}</td>
                <td>{$fila['nombre']} {$fila['apellidos']}</td>
                <td>{$fila['email']}</td>
                <td>$nombreRol</td>
                <td>
                    <a href="editar_usuario.php?id=$idU">[Cambiar Rol]</a> 
					<a href="includes/borrar_usuario.php?id=$idU" class="admin-usuarios-eliminar boton-borrar" data-mensaje="Esto borrará al usuario de la base de datos permanentemente. ¿Proceder?">[Borrar]</a>
                </td>
            </tr>
EOS;
    }

    $contenidoPrincipal = <<<EOS
        <h1>Panel de Administración</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Usuario</th><th>Nombre Completo</th><th>Email</th><th>Rol</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>$filas</tbody>
        </table>
		<script src="js/confirmacion_borrado.js"></script>
EOS;
}

require 'includes/vistas/plantillas/plantilla.php';