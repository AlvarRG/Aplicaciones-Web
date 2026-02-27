<?php

namespace es\ucm\fdi\aw;

class FormularioEditarCategoria extends Formulario
{
    private $idCategoria;

    public function __construct($idCategoria)
    {
        parent::__construct('formEditarCategoria', [
            'urlRedireccion' => 'admin_categorias.php?success=edit',
            'enctype' => 'multipart/form-data'
        ]);
        $this->idCategoria = $idCategoria;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $query = "SELECT * FROM categorias WHERE id = {$this->idCategoria}";
        $res = $conn->query($query);
        $cat = $res->fetch_assoc();

        $nombre = $datos['nombre'] ?? $cat['nombre'];
        $descripcion = $datos['descripcion'] ?? $cat['descripcion'];

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Editar Categoría</legend>
            
            <input type="hidden" name="id" value="{$this->idCategoria}">
            
            <div>
                <label>Nombre:</label>
                <input type="text" name="nombre" value="$nombre" required>
            </div>
            
            <div>
                <label>Descripción:</label><br>
                <textarea name="descripcion" rows="4" cols="50">$descripcion</textarea>
            </div>
            
            <div>
                <p>Imagen actual:</p>
                <img src="img/categorias/{$cat['imagen']}" width="80" style="border: 1px solid #ccc; border-radius: 5px;">
            </div>
            
            <div>
                <label>Cambiar imagen (opcional):</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
            <br>
            <button type="submit">Guardar Cambios</button>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $conn = Aplicacion::getInstance()->getConexionBd();

        $id = (int)$datos['id'];
        $nombre = $conn->real_escape_string($datos['nombre'] ?? '');
        $descripcion = $conn->real_escape_string($datos['descripcion'] ?? '');

        if (!$nombre) {
            $this->errores['nombre'] = "El nombre no puede estar vacío.";
        }

        if (count($this->errores) === 0) {
            // Recuperar imagen actual por si no se cambia
            $res = $conn->query("SELECT imagen FROM categorias WHERE id = $id");
            $fila = $res->fetch_assoc();
            $imagenFinal = $fila['imagen'];

            // Si se sube una nueva imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $dir = "img/categorias/";
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImg = "cat_" . $id . "_" . time() . "." . $ext;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
                    $imagenFinal = $nombreImg;
                }
            }

            // Actualizamos en BD
            $query = "UPDATE categorias SET nombre='$nombre', descripcion='$descripcion', imagen='$imagenFinal' WHERE id = $id";
            if (!$conn->query($query)) {
                $this->errores[] = "Error al actualizar la categoría: " . $conn->error;
            }
        }
    }
}
