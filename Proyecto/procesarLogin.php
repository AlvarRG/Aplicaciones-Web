<?php
session_start();
require_once __DIR__.'/utils.php';

$formEnviado = isset($_POST['login']);
if (! $formEnviado ) {
    header('Location: login.php');
    exit();
}

$erroresFormulario = [];
$nombreUsuario = filter_input(INPUT_POST, 'nombreUsuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$password = $_POST['password'] ?? '';

if (empty($nombreUsuario)) $erroresFormulario['nombreUsuario'] = 'El usuario no puede estar vacio';
if (empty($password)) $erroresFormulario['password'] = 'El password no puede estar vacio.';

if (count($erroresFormulario) === 0) {
    $conn = conexionBD();
    $query = sprintf("SELECT * FROM usuarios WHERE nombreUsuario = '%s'", $conn->real_escape_string($nombreUsuario));
    $rs = $conn->query($query);

    if ($rs && $rs->num_rows == 1) {
        $fila = $rs->fetch_assoc();
        if (password_verify($password, $fila['password'])) {
            
            $_SESSION['login'] = true;
            $_SESSION['id'] = $fila['id'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['nombreUsuario'] = $fila['nombreUsuario'];
            $_SESSION['avatar'] = $fila['avatar'] ?? 'default_avatar.png';

            $resRoles = $conn->query("SELECT rol FROM rolesusuario WHERE usuario = {$fila['id']}");
            $roles = [];
            while($r = $resRoles->fetch_assoc()) { 
                $roles[] = $r['rol']; 
            }
            
            $_SESSION['esAdmin'] = in_array(4, $roles);
            $_SESSION['esCamarero'] = in_array(2, $roles);
            $_SESSION['esCocinero'] = in_array(3, $roles);
            
            header('Location: index.php');
            exit();
        } else {
            $erroresFormulario[] = "El usuario o el password no coinciden";
        }
    } else {
        $erroresFormulario[] = "El usuario o el password no coinciden";
    }
}

$htmlErroresGlobales = generaErroresGlobalesFormulario($erroresFormulario);
$errorUsuario = generarError('nombreUsuario', $erroresFormulario);
$errorPassword = generarError('password', $erroresFormulario);
$tituloPagina = 'Error Login';
$contenidoPrincipal = <<<EOS
    <h1>Error de acceso</h1>
    $htmlErroresGlobales
    <form action="procesarLogin.php" method="POST">
        <fieldset>
            <div><label>Usuario:</label><input type="text" name="nombreUsuario" value="$nombreUsuario" />$errorUsuario</div>
            <div><label>Password:</label><input type="password" name="password" />$errorPassword</div>
            <button type="submit" name="login">Entrar</button>
        </fieldset>
    </form>
EOS;
require 'includes/vistas/plantillas/plantilla.php';