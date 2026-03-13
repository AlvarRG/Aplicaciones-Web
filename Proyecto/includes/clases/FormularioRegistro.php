<?php
namespace es\ucm\fdi\aw;

class FormularioRegistro extends Formulario
{
    /**
     * Constructor de la clase
     */
    public function __construct()
    {
		//Página a la que puede redirigir cuando tiene éxito
        parent::__construct('formRegistro', [
            'urlRedireccion' => 'index.php'
        ]);
    }

    /**
     * Genera los campos del formulario
     *
     * @param array $datos
     * @return string
     */
    protected function generaCamposFormulario(&$datos)
    {
        //Cogemos los datos
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

    /**
     * Procesa los datos del formulario
     *
     * @param array $datos
     * @return void
     */
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        //Tomamos las variables filtrando su contenido
        $nombreUsuario = htmlspecialchars(trim($datos['nombreUsuario'] ?? ''));
        $nombre = htmlspecialchars(trim($datos['nombre'] ?? ''));
        $apellidos = htmlspecialchars(trim($datos['apellidos'] ?? ''));
        $email = filter_var($datos['email'] ?? '', FILTER_SANITIZE_EMAIL);

        $password = $datos['password'] ?? '';
        $password2 = $datos['password2'] ?? '';

        //Validaciones básicas
        if ($password !== $password2) {
            $this->errores[] = "Las contraseñas no coinciden."; //<-- Corchetes vacíos
        }
        if (mb_strlen($password) < 5) {
            $this->errores[] = "La contraseña debe tener al menos 5 caracteres."; //<-- Corchetes vacíos
        }

        //Si no hay errores, comprobamos la BD
        if (count($this->errores) === 0) {
            //Comprobar si existe
            $disponible = Usuario::compruebaDisponibilidad($nombreUsuario, $email);
            if (!$disponible) {
                $this->errores[] = "El usuario o el email ya existen.";
            }
            else {
                //Inserción en la BD
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $usuarioCreado = Usuario::crea($nombreUsuario, $password, $nombre, Usuario::USER_ROLE, $apellidos, $email);
				//Si no ha habido problemas iniciamos la sesión del nuevo usuario
                if (!$usuarioCreado) {
                    $this->errores[] = "Error al registrar en la base de datos.";
                }
                else {
                    $_SESSION['login'] = true;
                    $_SESSION['id'] = $usuarioCreado->getId();
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['nombreUsuario'] = $nombreUsuario;
                    $_SESSION['esAdmin'] = $usuarioCreado->tieneRol(Usuario::ADMIN_ROLE);
                    $_SESSION['esCamarero'] = $usuarioCreado->tieneRol(Usuario::CAMARERO_ROLE);
                    $_SESSION['esCocinero'] = $usuarioCreado->tieneRol(Usuario::COCINERO_ROLE);
                }
            }
        }
    }
}