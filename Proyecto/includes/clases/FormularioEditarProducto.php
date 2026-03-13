<?php
namespace es\ucm\fdi\aw;

class FormularioEditarProducto extends Formulario
{
    private $idProducto;

    /**
     * Constructor de la clase
     *
     * @param int $idProducto
     */
    public function __construct($idProducto) {
		//Página a la que redirige cuando tiene éxito
        parent::__construct('formEditarProducto', [
            'urlRedireccion' => 'admin_productos.php?success=edit',
            'enctype' => 'multipart/form-data'
        ]);
        $this->idProducto = $idProducto;
    }

    /**
     * Genera los campos del formulario
     *
     * @param array $datos
     * @return string
     */
    protected function generaCamposFormulario(&$datos)
    {
        //Cogemos el producto
        $product = Producto::porId((int)$this->idProducto);

		//Preparamos las variables, si tenemos datos usamos esos, si no los que hemos consultado
        $nombre = $datos['nombre'] ?? $product['nombre'];
        $descripcion = $datos['descripcion'] ?? $product['descripcion'];
        $precio_base = $datos['precio_base'] ?? $product['precio_base'];
        $id_categoria_actual = $datos['id_categoria'] ?? $product['id_categoria'];
        $iva_actual = $datos['iva'] ?? $product['iva'];
        
       //Checkboxes de estado
        if (!empty($datos)) {
            $dispChecked = isset($datos['disponible']) ? 'checked' : '';
            $oferChecked = isset($datos['ofertado']) ? 'checked' : '';
        } else {
            $dispChecked = ($product['disponible'] == 1) ? 'checked' : '';
            $oferChecked = ($product['ofertado'] == 1) ? 'checked' : '';
        }

        //Generar el selector de categorías dinámico
        $categorias = Categoria::todas();
        $selectorCategorias = '<select name="id_categoria" required>';
        foreach ($categorias as $cat) {
            $selected = ($cat['id'] == $id_categoria_actual) ? 'selected' : '';
            $selectorCategorias .= "<option value='{$cat['id']}' $selected>{$cat['nombre']}</option>";
        }
        $selectorCategorias .= '</select>';

        //Atributos de IVA
        $sel4  = ($iva_actual == 4) ? 'selected' : '';
        $sel10 = ($iva_actual == 10) ? 'selected' : '';
        $sel21 = ($iva_actual == 21) ? 'selected' : '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $rutaImgs = RUTA_IMGS;

        return <<<EOF
        $htmlErroresGlobales
        <input type="hidden" name="id" value="{$this->idProducto}">
        
        <fieldset>
            <legend>Datos Principales</legend>
            <p>Nombre: <input type="text" name="nombre" value="$nombre" required></p>
            <p>Categoría: $selectorCategorias</p>
            <p>Descripción:<br>
               <textarea name="descripcion" rows="4" cols="50">$descripcion</textarea>
            </p>
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
                <strong>Precio Final Actualizado: <span id="p_final">0.00</span> €</strong>
            </p>
        </fieldset>

        <fieldset>
            <legend>Imagen y Disponibilidad</legend>
            <p>Imagen actual:<br>
               <img src="{$rutaImgs}/productos/{$product['imagen']}" width="100" class="form-imagen-actual">
            </p>
            <p>Cambiar imagen: <input type="file" name="imagen" accept="image/*"></p>
            
            <p><label><input type="checkbox" name="disponible" $dispChecked> Producto en Stock (Disponible)</label></p>
            <p><label><input type="checkbox" name="ofertado" $oferChecked> Visible en Carta (Ofertado)</label></p>
        </fieldset>

        <div class="form-editar-producto-acciones">
            <button type="submit">Actualizar Producto</button>
        </div>
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
        $nombre = (string)($datos['nombre'] ?? '');
        $descripcion = (string)($datos['descripcion'] ?? '');
        $id_categoria = (int)($datos['id_categoria'] ?? 0);
        $precio_base = (float)($datos['precio_base'] ?? 0);
        $iva = (int)($datos['iva'] ?? 10);
        
        $disponible = isset($datos['disponible']) ? 1 : 0;
        $ofertado = isset($datos['ofertado']) ? 1 : 0;

        //Validaciones
        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre del producto no puede estar vacío.";
        }
        if ($precio_base < 0) {
            $this->errores['precio_base'] = "El precio no puede ser negativo.";
        }

        if (count($this->errores) === 0) {
            //Recuperar imagen actual
            $productoActual = Producto::porId($id);
            $imagenFinal = $productoActual['imagen'] ?? 'prod_default.png';

            //Gestión de nueva imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $dir = RAIZ_APP . "/img/productos/";
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImg = "prod_" . $id . "_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImg)) {
                    $imagenFinal = $nombreImg;
                }
            }

			//Actualizar la BD
            Producto::actualizar(
                $id,
                $id_categoria,
                $nombre,
                $descripcion,
                $precio_base,
                $iva,
                $disponible,
                $ofertado,
                $imagenFinal
            );
        }
    }
}