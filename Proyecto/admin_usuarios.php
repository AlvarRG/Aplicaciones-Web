<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Gestión de Usuarios';

if (!isset($_SESSION['esAdmin']) || !$_SESSION['esAdmin']) {
    $contenidoPrincipal = "<h1>Acceso Denegado</h1><p>Solo el Gerente puede ver esto.</p>";
} else {
    $conn = Aplicacion::getInstance()->getConexionBd();
    $query = "SELECT id, nombreUsuario, nombre, apellidos, email FROM Usuarios";
    $rs = $conn->query($query);
    
    $filas = "";
    while ($fila = $rs->fetch_assoc()) {
        $idU = $fila['id'];
        // Obtenemos el nombre del rol
        $resRol = $conn->query("SELECT R.nombre FROM Roles R JOIN RolesUsuario RU ON R.id = RU.rol WHERE RU.usuario = $idU");
        $roles = [];
        while($r = $resRol->fetch_assoc()) { $roles[] = $r['nombre']; }
        $rolesStr = implode(', ', $roles);

        $filas .= <<<EOS
            <tr>
                <td>{$fila['nombreUsuario']}</td>
                <td>{$fila['nombre']} {$fila['apellidos']}</td>
                <td>{$fila['email']}</td>
                <td>$rolesStr</td>
                <td>
                    <a href="editar_usuario.php?id=$idU">[Cambiar Rol]</a> 
                    <a href="borrar_usuario.php?id=$idU" onclick="return confirm('¿Estás seguro?')">[Borrar]</a>
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
EOS;
}

require 'includes/vistas/plantillas/plantilla.php';