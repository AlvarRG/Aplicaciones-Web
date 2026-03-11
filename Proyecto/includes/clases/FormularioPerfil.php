<?php

namespace es\ucm\fdi\aw;

class FormularioPerfil extends Formulario
{
    private $nombreUsuario;

    public function __construct($nombreUsuario)
    {
        parent::__construct('formPerfil', [
            'action'  => 'perfil.php',
            'enctype' => 'multipart/form-data'
        ]);
        $this->nombreUsuario = $nombreUsuario;
    }

    /**
     * Genera y devuelve el HTML completo del formulario de perfil (incluyendo el <form>)
     * para ser embebido directamente en el layout de perfil.php.
     */
    public function generaHtml()
    {
        $this->errores = [];
        $datos = $_POST ?: [];
        // Si el formulario fue enviado, lo procesamos (puede poblar $this->errores)
        if ($this->formularioEnviado($datos)) {
            $this->procesaFormulario($datos);
        }
        return $this->generaCamposFormulario($datos);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // 1. Obtenemos los datos actuales del usuario
        $queryUsuarioPerfil = "SELECT * FROM usuarios WHERE nombreUsuario = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryUsuarioPerfil, "s", (string)$this->nombreUsuario)->get_result();
        $user = $rs ? $rs->fetch_assoc() : null;
        if ($rs) {
            $rs->free();
        }

        // 2. Preparamos las variables (Prioridad: lo escrito tras un error > lo que hay en BD)
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

        // 3. Retornamos el HTML del formulario con las clases CSS del diseño de perfil.php
        return <<<EOF
        $htmlErrores
        <form action="perfil.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="formId" value="formPerfil">
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
        </form>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // 1. Recoger textos
        $nombre    = (string)($datos['nombre']     ?? '');
        $apellidos = (string)($datos['apellidos']  ?? '');
        $email     = (string)($datos['email']      ?? '');
        $avatarFinal = (string)($datos['avatar_pre'] ?? 'default.png');

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

            // 4. Actualizar BD
            $queryUpdatePerfil = "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, avatar = ? WHERE nombreUsuario = ?";
            Aplicacion::getInstance()->ejecutarConsultaBd(
                $queryUpdatePerfil,
                "sssss",
                $nombre,
                $apellidos,
                $email,
                $avatarFinal,
                (string)$this->nombreUsuario
            );

            $_SESSION['nombre'] = $nombre;
            // Redireccionamos para evitar reenvío del formulario (PRG pattern)
            header("Location: perfil.php?success=1");
            exit();
        }
    }
}
