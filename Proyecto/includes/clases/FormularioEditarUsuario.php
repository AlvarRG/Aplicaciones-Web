<?php
namespace es\ucm\fdi\aw;

class FormularioEditarUsuario extends Formulario
{
    private $idUsuario;

    /**
     * Constructor de la clase
     *
     * @param int $idUsuario
     */
    public function __construct($idUsuario) {
        //Página a la que redirige cuando tiene éxito
        parent::__construct('formEditarUsuario', [
            'urlRedireccion' => 'admin_usuarios.php?success=edit'
        ]);
        $this->idUsuario = $idUsuario;
    }

    /**
     * Genera los campos del formulario
     *
     * @param array $datos
     * @return string
     */
    protected function generaCamposFormulario(&$datos)
    {
        //Cogemos el rol
        $rolActual = 1; //Por defecto Cliente
        if (empty($datos)) { //Si es la primera vez que carga (no hay POST)
            $usuarioObj = Usuario::buscaPorId((int)$this->idUsuario);
            if ($usuarioObj) {
                $rolActual = $usuarioObj->getRol();
            }
        } else {
            //Si hubo error, mantenemos el que intentó guardar
            $rolActual = $datos['nuevo_rol'] ?? 1;
        }

        //Marcamos la opción correspondiente
        $sel1 = ($rolActual == 1) ? 'selected' : '';
        $sel2 = ($rolActual == 2) ? 'selected' : '';
        $sel3 = ($rolActual == 3) ? 'selected' : '';
        $sel4 = ($rolActual == 4) ? 'selected' : '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <input type="hidden" name="id" value="{$this->idUsuario}">
        
        <fieldset class="form-editar-usuario">
            <legend>Asignación de Permisos</legend>
            <p>
                <label>Selecciona el nuevo rol principal:</label><br><br>
                <select name="nuevo_rol" class="form-editar-usuario-select">
                    <option value="1" $sel1>Cliente (Básico)</option>
                    <option value="2" $sel2>Camarero</option>
                    <option value="3" $sel3>Cocinero</option>
                    <option value="4" $sel4>Gerente (Admin)</option>
                </select>
            </p>
            <button type="submit" class="form-editar-usuario-submit">Guardar Cambios</button>
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
		//Tomamos los datos
        $this->errores = [];
        $id = (int)$datos['id'];
        $nuevoRol = (int)$datos['nuevo_rol'];

		//Validación de los datos
        if ($id <= 0 || $nuevoRol <= 0) {
            $this->errores[] = "Datos de rol inválidos.";
        }

		//Si no ha habido errores actualizamos el rol
        if (count($this->errores) === 0) {
            Usuario::actualizarRol($id, $nuevoRol);
        }
    }
}