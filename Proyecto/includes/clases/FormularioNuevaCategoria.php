<?php
namespace es\ucm\fdi\aw;

class FormularioNuevaCategoria extends Formulario
{
    public function __construct() {
        parent::__construct('formNuevaCategoria', [
            'urlRedireccion' => 'admin_categorias.php',
            'enctype' => 'multipart/form-data'
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre = $datos['nombre'] ?? '';
        $descripcion = $datos['descripcion'] ?? '';
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Datos de la categoría</legend>
            <div>
                <label>Nombre:</label>
                <input type="text" name="nombre" value="$nombre" required>
            </div>
            <div>
                <label>Descripción:</label><br>
                <textarea name="descripcion" rows="4" cols="50">$descripcion</textarea>
            </div>
            <div>
                <label>Imagen (Icono):</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
            <br>
            <button type="submit">Guardar Categoría</button>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $conn = Aplicacion::getInstance()->getConexionBd();

        $nombre = $conn->real_escape_string($datos['nombre'] ?? '');
        $descripcion = $conn->real_escape_string($datos['descripcion'] ?? '');
        $imagen = 'cat_default.png';

        if (!$nombre) {
            $this->errores['nombre'] = "El nombre es obligatorio.";
        }

        if (count($this->errores) === 0) {
            // Lógica de subida de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $dir = "img/categorias/";
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImg = "cat_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
                    $imagen = $nombreImg;
                }
            }
            
            $query = "INSERT INTO categorias (nombre, descripcion, imagen) VALUES ('$nombre', '$descripcion', '$imagen')";
            
            if (!$conn->query($query)) {
                $this->errores[] = "Error al crear categoría: " . $conn->error;
            }
        }
    }
}