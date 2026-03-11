<?php
namespace es\ucm\fdi\aw;

class FormularioNuevoProducto extends Formulario
{
    /**
     * Constructor de la clase
     */
    public function __construct() {
		
		//Página a la que redirige cuando tiene éxito
        parent::__construct('formNuevoProducto', [
            'urlRedireccion' => 'admin_productos.php?success=1',
            'enctype' => 'multipart/form-data'
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
        $descripcion = $datos['descripcion'] ?? '';
        $precio_base = $datos['precio_base'] ?? '';
        $id_categoria_seleccionada = $datos['id_categoria'] ?? '';
        $iva_seleccionado = $datos['iva'] ?? 10;
        
        $dispChecked = (empty($datos) || isset($datos['disponible'])) ? 'checked' : '';

        //Cogemos todas las categorías para el selector
        $categorias = Categoria::todas();
        $selectorCategorias = '<select name="id_categoria" required>';
        foreach ($categorias as $cat) {
            $selected = ($cat['id'] == $id_categoria_seleccionada) ? 'selected' : '';
            $selectorCategorias .= "<option value='{$cat['id']}' $selected>{$cat['nombre']}</option>";
        }
        $selectorCategorias .= '</select>';
		
		//Atributos de IVA
        $sel4  = ($iva_seleccionado == 4) ? 'selected' : '';
        $sel10 = ($iva_seleccionado == 10) ? 'selected' : '';
        $sel21 = ($iva_seleccionado == 21) ? 'selected' : '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Información General</legend>
            <p>Nombre: <input type="text" name="nombre" value="$nombre" required></p>
            <p>Categoría: $selectorCategorias</p>
            <p>Descripción:<br><textarea name="descripcion" rows="4" cols="50">$descripcion</textarea></p>
        </fieldset>

        <fieldset>
            <legend>Precio y Tasas</legend>
            <p>Precio Base (€): 
               <input type="number" step="0.01" id="p_base" name="precio_base" value="$precio_base" required>
            </p>
            <p>IVA (%): 
                <select id="p_iva" name="iva">
                    <option value="4" $sel4>4% (Superreducido)</option>
                    <option value="10" $sel10>10% (Hostelería)</option>
                    <option value="21" $sel21>21% (General)</option>
                </select>
            </p>
            <p class="form-precio-final">
                <strong>Precio Final (con IVA): <span id="p_final">0.00</span> €</strong>
            </p>
        </fieldset>

        <fieldset>
            <legend>Imagen y Disponibilidad</legend>
            <p>Imágenes: <input type="file" name="imagen" accept="image/*"></p>
            <p><label><input type="checkbox" name="disponible" $dispChecked> ¿Hay stock disponible?</label></p>
        </fieldset>

        <button type="submit">Dar de alta producto</button>
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
        $nombre = (string)($datos['nombre'] ?? '');
        $id_categoria = (int)($datos['id_categoria'] ?? 0);
        $descripcion = (string)($datos['descripcion'] ?? '');
        $precio_base = (float)($datos['precio_base'] ?? 0);
        $iva = (int)($datos['iva'] ?? 10);
        
        //Checkbox: si no se marca, no llega en el POST
        $disponible = isset($datos['disponible']) ? 1 : 0;
        $ofertado = 1; //Por defecto al crearlo está en la carta

        //Validaciones básicas
        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre es obligatorio.";
        }
        if ($precio_base < 0) {
            $this->errores['precio_base'] = "El precio no puede ser negativo.";
        }

        //Si no hay errores, subimos foto y guardamos
        if (count($this->errores) === 0) {
            $imagen = 'prod_default.png';
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $dir = "img/productos/";
                  //Creamos la carpeta si no existe (por seguridad)
                if (!file_exists($dir)) { mkdir($dir, 0777, true); }
                
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImg = "prod_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
                    $imagen = $nombreImg;
                }
            }

            //Inserción en la BD
            Producto::crear(
                $id_categoria,
                $nombre,
                $descripcion,
                $precio_base,
                $iva,
                $disponible,
                $ofertado,
                $imagen
            );
        }
    }
}