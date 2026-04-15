<?php
namespace es\ucm\fdi\aw\productos;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;
use es\ucm\fdi\aw\Aplicacion;


class FormularioEditarOferta extends Formulario
{
    private $idOferta;

    /**
     * Constructor de la clase
     *
     * @param int $idOferta
     */
    public function __construct($idOferta) {
		//Página a la que redirige cuando tiene éxito
        parent::__construct('formEditarOferta', [
            'urlRedireccion' => 'admin_ofertas.php?success=edit',
            'enctype' => 'multipart/form-data'
        ]);
        $this->idOferta = $idOferta;
    }

    /**
     * Genera los campos del formulario
     *
     * @param array $datos
     * @return string
     */
    protected function generaCamposFormulario(&$datos)
    {
        //Cogemos la oferta
        $oferta = Oferta::todasLasOfertas((int)$this->idOferta);

		//Preparamos las variables, si tenemos datos usamos esos, si no los que hemos consultado
        $nombre = $datos['nombre'] ?? $oferta['nombre'];
        $descripcion = $datos['descripcion'] ?? $oferta['descripcion'];
        $fecha_inicio = $datos['fecha_inicio'] ?? $oferta['fecha_inicio'];
        $fecha_fin = $datos['fecha_fin'] ?? $product['fecha_fin'];
        $descuento = $datos['descuento'] ?? $product['descuento'];
		
		$id_producto_seleccionado = $datos['id_producto'] ?? '';
        $productos = Producto::todosConCategoria();
		$selectorProductos = '<select multiple class="select-multiple">';
		foreach ($productos as $prod) {
            $precioIva = Producto::calcularPrecioConIva($prod['precio_base'], $prod['iva']);
			$selectorProductos .= "<option value='{$prod['id']}' data-precio='{$precioIva}' data-nombre='{$prod['nombre']}'>{$prod['nombre']} ({$precioIva}€)</option>";
		}
        $selectorProductos .= '</select>';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
        $htmlErroresGlobales
        <input type="hidden" name="id" value="{$this->idOferta}">
        
        <fieldset>
            <legend>Datos Principales</legend>
            <p>Nombre: <input type="text" name="nombre" value="$nombre" required></p>
            <p>Descripción:<br>
               <textarea name="descripcion" rows="4" cols="50">$descripcion</textarea>
            </p>
			<p><em>Haz un doble click en los productos de la izquierda para añadirlos a la oferta:</em></p>
			<div style="display: flex; gap: 30px; align-items: flex-start; margin-bottom: 20px;">
				<div>$selectorProductos</div>
				
				<div>
					<table id="tablaProductosOferta" border="1" style="min-width: 300px; text-align: center;">
						<thead style="background: #eee;">
							<tr>
								<th>Producto</th>
								<th>Cant.</th>
								<th>Subtotal</th>
								<th>Quitar</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
					<p style="text-align: right;"><strong>Precio Normal Total: <span id="precioTotalNormal">0.00</span>€</strong></p>
				</div>
			</div>
        </fieldset>

        <fieldset>
            <legend>Condiciones de la Oferta</legend>
			<p>Precio Final (€): 
			   <input type="number" step="0.01" min="0" id="precio_final" name="precio_final" value="">
			</p>
			<p>Porcentaje de Descuento (%): 
			   <input type="number" step="0.01" min="0" max="100" id="descuento" name="descuento" value="$descuento" required>
			</p>
			<p>Fecha de Inicio: 
			   <input type="date" id="fecha_inicio" name="fecha_inicio" value="$fecha_inicio" required>
			</p>
			<p>Fecha de Fin: 
			   <input type="date" id="fecha_fin" name="fecha_fin" value="$fecha_fin" required>
			</p>
        </fieldset>

        <div class="form-editar-oferta-acciones">
            <button type="submit">Actualizar Oferta</button>
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