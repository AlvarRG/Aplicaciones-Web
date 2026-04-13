<?php
namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;
use es\ucm\fdi\aw\Aplicacion;


class FormularioNuevaOferta extends Formulario
{
    /**
     * Constructor de la clase
     */
    public function __construct() {
		
		//Página a la que redirige cuando tiene éxito
        parent::__construct('formNuevoProducto', [
            'urlRedireccion' => 'admin_ofertas.php?success=1',
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
        $productos = Producto::todosConCategoria();
		$selectorProductos = '<select name="id_producto" required>';
		foreach ($productos as $prod) {
			$selected = ($prod['id'] == id_producto_seleccionado) ? 'selected' : '';
			$selectorProductos .= "<option value='{$cat['id']}' $selected>{$cat['nombre']}</option>";
        }
		$precioFinal = $datos['precioFinal'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Información General</legend>
            <p>Nombre: <input type="text" name="nombre" value="$nombre" required></p>
            <p>Productos: $selectorProductos</p>
            <p>Descripción:<br><textarea name="descripcion" rows="4" cols="50">$descripcion</textarea></p>
        </fieldset>

        <fieldset>
            <legend>Precio y Tasas</legend>
            <p>Precio Final (€): 
               <input type="number" step="0.01" id="p_final" name="precio_final" value="$precioFinal" required>
            </p>
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