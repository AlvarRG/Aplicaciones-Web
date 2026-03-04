<?php
namespace es\ucm\fdi\aw;

class FormularioRegistro extends Formulario
{
    public function __construct() {
        parent::__construct('formRegistro', [
            'urlRedireccion' => 'index.php' // Adónde va si todo sale bien
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Recuperar datos si hubo un error de validación
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';
        $nombreUsuario = $datos['nombreUsuario'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Datos Personales</legend>
            <div>
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" required />
            </div>
            <div>
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos" required />
            </div>
            <div>
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="$email" required />
            </div>
        </fieldset>
        <br>
        <fieldset>
            <legend>Datos de Cuenta</legend>
            <div>
                <label for="nombreUsuario">Nombre de usuario:</label>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" required />
            </div>
            <div>
                <label for="password">Password:</label>
                <input id="password" type="password" name="password" required />
            </div>
            <div>
                <label for="password2">Reintroduce el password:</label>
                <input id="password2" type="password" name="password2" required />
            </div>
            <div>
                <button type="submit">Registrar</button>
            </div>
        </fieldset>
EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // 1. Saneamiento (como tenías en tu filter_input)
        $nombreUsuario = htmlspecialchars(trim($datos['nombreUsuario'] ?? ''));
        $nombre = htmlspecialchars(trim($datos['nombre'] ?? ''));
        $apellidos = htmlspecialchars(trim($datos['apellidos'] ?? ''));
        $email = filter_var($datos['email'] ?? '', FILTER_SANITIZE_EMAIL);
        
        $password = $datos['password'] ?? '';
        $password2 = $datos['password2'] ?? '';

       // 2. Validaciones básicas
        if ($password !== $password2) {
            $this->errores[] = "Las contraseñas no coinciden."; // <-- Corchetes vacíos
        }
        if (mb_strlen($password) < 5) {
            $this->errores[] = "La contraseña debe tener al menos 5 caracteres."; // <-- Corchetes vacíos
        }

        // Si no hay errores, comprobamos la BD
        if (count($this->errores) === 0) {
            $conn = Aplicacion::getInstance()->getConexionBd();
            $u = $conn->real_escape_string($nombreUsuario);
            $e = $conn->real_escape_string($email);

            // Comprobar si existe
            $check = $conn->query("SELECT id FROM usuarios WHERE nombreUsuario='$u' OR email='$e'");
            
            if ($check->num_rows > 0) {
                $this->errores[] = "El usuario o el email ya existen.";
            } else {
                // Inserción en la BD
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO usuarios(nombreUsuario, nombre, apellidos, email, password, avatar, rol) 
                          VALUES ('$u', '$nombre', '$apellidos', '$e', '$passHash', 'default.png', 1)";

                if ($conn->query($query)) {
                    $_SESSION['login'] = true;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['nombreUsuario'] = $nombreUsuario;
                    $_SESSION['esAdmin'] = false;
                    
                } else {
                    $this->errores[] = "Error al registrar en la base de datos.";
                }
            }
        }
    }
}