<?php
namespace es\ucm\fdi\aw;

class FormularioEditarUsuario extends Formulario
{
    private $idUsuario;

    public function __construct($idUsuario) {
        // Redirige al panel de usuarios tras el éxito
        parent::__construct('formEditarUsuario', [
            'urlRedireccion' => 'admin_usuarios.php?success=edit'
        ]);
        $this->idUsuario = $idUsuario;
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Extra de UX: Obtener el rol actual para preseleccionarlo en el menú
        $rolActual = 1; // Por defecto Cliente
        if (empty($datos)) { // Si es la primera vez que carga (no hay POST)
            $queryRolUsuario = "SELECT rol FROM usuarios WHERE id = ? LIMIT 1";
            $rsRol = Aplicacion::getInstance()->ejecutarConsultaBd($queryRolUsuario, "i", (int)$this->idUsuario)->get_result();
            if ($rsRol && $rsRol->num_rows > 0) {
                $rolActual = (int)$rsRol->fetch_assoc()['rol'];
            }
            if ($rsRol) {
                $rsRol->free();
            }
        } else {
            // Si hubo error, mantenemos el que intentó guardar
            $rolActual = $datos['nuevo_rol'] ?? 1;
        }

        // Marcamos la opción correspondiente
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

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $id = (int)$datos['id'];
        $nuevoRol = (int)$datos['nuevo_rol'];

        if ($id <= 0 || $nuevoRol <= 0) {
            $this->errores[] = "Datos de rol inválidos.";
        }

        if (count($this->errores) === 0) {
            // En este sistema simple, un usuario tiene un solo rol principal.
            // Actualizamos el valor en la tabla usuarios.
            $queryUpdateRolUsuario = "UPDATE usuarios SET rol = ? WHERE id = ?";
            Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateRolUsuario, "ii", $nuevoRol, $id);
        }
    }
}