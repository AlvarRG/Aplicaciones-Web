<?php

namespace es\ucm\fdi\aw;

class FormularioPerfil extends Formulario
{
    private $nombreUsuario;

    public function __construct($nombreUsuario)
    {
		//Página a la que puede redirigir cuando tiene éxito
        parent::__construct('formPerfil', [
            'action'  => 'perfil.php',
            'enctype' => 'multipart/form-data',
            'urlRedireccion' => 'perfil.php?success=1'
        ]);
        $this->nombreUsuario = $nombreUsuario;
    }



    protected function generaCamposFormulario(&$datos)
    {
        //Cogemos el usuario
        $usuario = Usuario::buscaUsuario($this->nombreUsuario);
        $user = null;
        if ($usuario) {
            $user = [
                'nombre'   => $usuario->getNombre(),
                'apellidos'=> $usuario->getApellidos(),
                'email'    => $usuario->getEmail(),
                'avatar'   => $usuario->getAvatar(),
            ];
        }

        //Preparamos las variables, si tenemos datos usamos esos, si no los que hemos consultado
        $nombre    = htmlspecialchars($datos['nombre']     ?? $user['nombre']);
        $apellidos = htmlspecialchars($datos['apellidos']  ?? $user['apellidos']);
        $email     = htmlspecialchars($datos['email']      ?? $user['email']);
        $avatarActual = $datos['avatar_pre'] ?? $user['avatar'];

        $avatares = ['alvar.jpg', 'ethan.jpg', 'yago.jpg', 'zhirun.jpg'];
        $htmlAvatares = "";
        foreach ($avatares as $av) {
            $checked = ($avatarActual == $av) ? "checked" : "";
            $htmlAvatares .= "<label class='perfil-avatar-opcion'><img src='img/avatares/$av'><input type='radio' name='avatar_pre' value='$av' $checked></label>";
        }

        $htmlErrores = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErrores
        <fieldset class="perfil-fieldset">
            <legend class="perfil-legend">Actualizar mis datos</legend>
            <p>Nombre:<br><input type="text" name="nombre" value="$nombre" class="perfil-input-text" required></p>
            <p>Apellidos:<br><input type="text" name="apellidos" value="$apellidos" class="perfil-input-text"></p>
            <p>Email:<br><input type="email" name="email" value="$email" class="perfil-input-text" required></p>

            <h4 class="perfil-avatar-title">Cambiar Avatar</h4>
            <div class="perfil-avatar-box">$htmlAvatares</div>
            <p>O sube uno propio:<br><input type="file" name="nueva_foto" accept="image/*" class="perfil-file-input"></p>
            <p class="perfil-checkbox"><input type="checkbox" name="borrar_foto"> Usar foto por defecto</p>

            <button type="submit" name="actualizar" class="perfil-submit">Guardar Cambios</button>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        //Tomamos los datos
        $nombre    = (string)($datos['nombre']     ?? '');
        $apellidos = (string)($datos['apellidos']  ?? '');
        $email     = (string)($datos['email']      ?? '');
        $avatarFinal = (string)($datos['avatar_pre'] ?? 'default.png');

        //Validaciones básicas
        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre no puede estar vacío.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = "El formato del email no es válido.";
        }

        if (count($this->errores) === 0) {
            //Lógica del Avatar
            if (isset($datos['borrar_foto'])) {
                $avatarFinal = 'default.png';
            } elseif (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] === UPLOAD_ERR_OK) {
                $directorio  = "img/avatares/";
                $extension   = pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $this->nombreUsuario . "_" . time() . "." . $extension;
                $rutaDestino = $directorio . $nombreArchivo;

                if (move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $rutaDestino)) {
                    $avatarFinal = $nombreArchivo;
                }
            }

            //Actualizar BD
            Usuario::actualizarPerfil(
                (string)$this->nombreUsuario,
                $nombre,
                $apellidos,
                $email,
                $avatarFinal
            );

            $_SESSION['nombre'] = $nombre;
        }
    }
}
