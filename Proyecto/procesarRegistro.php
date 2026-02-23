<?php
session_start();
require_once __DIR__.'/utils.php';

if (!isset($_POST['registro'])) { header('Location: registro.php'); exit(); }

$erroresFormulario = [];
$nombreUsuario = filter_input(INPUT_POST, 'nombreUsuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (count($erroresFormulario) === 0) {
    $conn = conexionBD();
    $u = $conn->real_escape_string($nombreUsuario);
    $e = $conn->real_escape_string($email);
    
    // Comprobar si existe
    $check = $conn->query("SELECT id FROM Usuarios WHERE nombreUsuario='$u' OR email='$e'");
    if ($check->num_rows > 0) {
        $erroresFormulario[] = "El usuario o el email ya existen.";
    } else {
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO Usuarios(nombreUsuario, nombre, apellidos, email, password, avatar) 
                  VALUES ('$u', '$nombre', '$apellidos', '$e', '$passHash', 'default.png')";
        
        if ($conn->query($query)) {
            $id = $conn->insert_id;
            $conn->query("INSERT INTO RolesUsuario(usuario, rol) VALUES ($id, ".USER_ROLE.")");
            
            $_SESSION['login'] = true;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['nombreUsuario'] = $nombreUsuario; // <-- LÍNEA CLAVE
            $_SESSION['esAdmin'] = false;
            
            header('Location: index.php');
            exit();
        }
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