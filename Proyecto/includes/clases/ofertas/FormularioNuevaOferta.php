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
        $nombre = $datos['nombre'] ?? '';
        $descripcion = $datos['descripcion'] ?? '';
        $fecha_inicio = $datos['fecha_inicio'] ?? '';
        $fecha_fin = $datos['fecha_fin'] ?? '';
        $descuento = $datos['descuento'] ?? '';
        $id_producto_seleccionado = $datos['id_producto'] ?? '';

        $productos = Producto::todosConCategoria();
		
		$selectorProductos = '<select multiple class="select-multiple">';
		foreach ($productos as $prod) {
            $precioIva = $prod->getPrecioConIva();
			$selectorProductos .= "<option value='{$prod->getId()}' data-precio='{$precioIva}' data-nombre='{$prod->getNombre()}'>{$prod->getNombre()} ({$precioIva}€)</option>";
		}
        $selectorProductos .= '</select>';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        return <<<EOF
$htmlErroresGlobales
<fieldset>
    <legend>Información General e Inclusión de Productos</legend>
    <p>Título de la Oferta: <input type="text" name="nombre" value="$nombre" required></p>
    <p>Descripción:<br><textarea name="descripcion" rows="4" cols="50">$descripcion</textarea></p>
    
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
<button type="submit">Dar de alta oferta</button>
<script defer src="js/nueva_oferta.js"></script>
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
        $descripcion = (string)($datos['descripcion'] ?? '');
		$fecha_inicio = (string)($datos['fecha_inicio'] ?? '');
		$fecha_fin = (string)($datos['fecha_fin'] ?? '');
        $descuento = (int)($datos['descuento'] ?? 10);
		$id_productos = $datos['id_productos'] ?? [];
		$cantidades = $datos['cantidades'] ?? [];

        //Validaciones básicas
        if (empty($nombre)) {
            $this->errores['nombre'] = "El nombre es obligatorio.";
        }
		if (empty($id_productos)) {
			$this->errores['id_productos'] = "Debes seleccionar al menos un producto.";
		}
		if ($fecha_fin < $fecha_inicio) {
			$this->errores['fecha'] = "La fecha de fin debe ser mayor a la fecha de inicio";
		}
        //Si no hay errores, guardamos
        if (count($this->errores) === 0) {
            //Inserción en la BD
            $exito = Oferta::crear(
                $nombre,
                $descripcion,
				$fecha_inicio,
				$fecha_fin,
                $descuento,
				$id_productos,
				$cantidades
            );
			if (!$exito) {
				$this->errores[] = "Error al guardar la oferta en la base de datos.";
			}
        }
    }
}