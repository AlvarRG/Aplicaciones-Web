<?php

namespace es\ucm\fdi\aw;

class FormularioPerfil extends Formulario
{
    private $nombreUsuario;

    public function __construct($nombreUsuario)
    {
        parent::__construct('formPerfil', [
            'urlRedireccion' => 'perfil.php?success=1',
            'enctype' => 'multipart/form-data' // Imprescindible para subir la nueva_foto
        ]);
        $this->nombreUsuario = $nombreUsuario;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        // 1. Obtenemos los datos actuales del usuario
        $query = "SELECT * FROM usuarios WHERE nombreUsuario = '{$this->nombreUsuario}'";
        $rs = $conn->query($query);
        $user = $rs->fetch_assoc();

        // 2. Preparamos las variables (Prioridad: lo escrito tras un error > lo que hay en BD)
        $nombre = $datos['nombre'] ?? $user['nombre'];
        $apellidos = $datos['apellidos'] ?? $user['apellidos'];
        $email = $datos['email'] ?? $user['email'];
        $avatarActual = $datos['avatar_pre'] ?? $user['avatar'];

        $avatares = ['alvar.jpg', 'ethan.jpg', 'yago.jpg', 'zhirun.jpg'];
        $htmlAvatares = "";
        foreach ($avatares as $av) {
            $checked = ($avatarActual == $av) ? "checked" : "";
            $htmlAvatares .= "<label class='perfil-avatar-opcion'>
                    <img src='img/avatares/$av' width='40' height='40'>
                    <input type='radio' name='avatar_pre' value='$av' $checked>
                  </label>";
        }

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        // 4. Retornamos el HTML del formulario
        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Actualizar mis datos</legend>
            <p>Nombre: <input type="text" name="nombre" value="$nombre" required></p>
            <p>Apellidos: <input type="text" name="apellidos" value="$apellidos"></p>
            <p>Email: <input type="email" name="email" value="$email" required></p>
            
            <h4>Cambiar Avatar</h4>
            <div>$htmlAvatares</div>
            
            <p>O sube uno propio: <input type="file" name="nueva_foto" accept="image/*"></p>
            <p><label><input type="checkbox" name="borrar_foto"> Usar foto por defecto</label></p>
            
            <button type="submit">Guardar Cambios</button>
        </fieldset>
    EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $conn = Aplicacion::getInstance()->getConexionBd();

       // 1. Recoger textos
        $nombre = $conn->real_escape_string($datos['nombre'] ?? '');
        $apellidos = $conn->real_escape_string($datos['apellidos'] ?? '');
        $email = $conn->real_escape_string($datos['email'] ?? '');
        $avatarFinal = $conn->real_escape_string($datos['avatar_pre'] ?? 'default.png');

        // 2. Validaciones básicas
        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre no puede estar vacío.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = "El formato del email no es válido.";
        }

        if (count($this->errores) === 0) {
            // 3. Lógica del Avatar
            if (isset($datos['borrar_foto'])) {
                $avatarFinal = 'default.png'; // Gana el checkbox de borrar
            } else if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] === UPLOAD_ERR_OK) {
                // Si ha subido un archivo
                $directorio = "img/avatares/";
                $extension = pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $this->nombreUsuario . "_" . time() . "." . $extension;
                $rutaDestino = $directorio . $nombreArchivo;

                if (move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $rutaDestino)) {
                    $avatarFinal = $nombreArchivo;
                }
            }

            // 4. Actualizar BD
            $query = "UPDATE usuarios SET nombre='$nombre', apellidos='$apellidos', email='$email', avatar='$avatarFinal' WHERE nombreUsuario='{$this->nombreUsuario}'";

            if ($conn->query($query)) {
                $_SESSION['nombre'] = $nombre;
            } else {
                $this->errores[] = "Error al actualizar tu perfil: " . $conn->error;
            }
        }
    }
}
