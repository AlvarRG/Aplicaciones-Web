<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

//Si no hay sesión iniciada te lleva al login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

//Variables para distinguir las sesiones
$esCamarero = isset($_SESSION['esCamarero']) ? $_SESSION['esCamarero'] : false;
$esAdmin = isset($_SESSION['esAdmin']) ? $_SESSION['esAdmin'] : false;

//Si no eres camarero ni admin te manda al inicio
if (!$esCamarero && !$esAdmin) {
    header('Location: index.php');
    exit();
}

//Gestionar cambio de estado en los pedidos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $idPed = (int)$_POST['id_pedido'];
    $nuevoEst = (string)$_POST['nuevo_estado'];
    $queryUpdateEstadoPedido = "UPDATE Pedidos SET estado = ? WHERE id = ?";
	Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateEstadoPedido, "si", $nuevoEst, $idPed);
    header('Location: tablet_camarero.php');
    exit();
}

//Título y estilos
$tituloPagina = 'Tablet Camarero';
$estilosExtra = ['tablet_camarero.css'];

//Consulta para coger los pedidos con estados recibido, listo cocina y terminado desde la base de datos
$queryPedidos = "SELECT id, numero_pedido, tipo, total, estado FROM Pedidos 
                 WHERE estado IN ('Recibido', 'Listo cocina', 'Terminado') ORDER BY fecha ASC";
$rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidos)->get_result();

//Si la consulta anterior ha devuelto algo, recorremos los pedidos devueltos para estructurar los arrays que se usarán posteriormente
$pedidos = [];
$idsPedidos = [];
if ($rs && $rs->num_rows > 0) {
    while ($fila = $rs->fetch_assoc()) {
		//Guardamos los pedidos con su id como clave
        $pedidos[$fila['id']] = $fila;
		//Creamos un array vacío en cada pedido para posteriormente almacenar ahí los productos
        $pedidos[$fila['id']]['productos'] = [];
		//Guardamos los ids de los pedidos
        $idsPedidos[] = $fila['id'];
    }
	$rs->free();
}

//Si hay algún pedido
if (!empty($idsPedidos)) {
	//Consulta a la base de datos que devuelve la información de todos los productos de todos los pedidos
    $placeholders = implode(',', array_fill(0, count($idsPedidos), '?'));
	//Consulta a la base de datos que devuelve la información de todos los productos de todos los pedidos
    $queryProductosPedidos = "SELECT pp.id_pedido, pp.cantidad, pp.precio_unitario, p.nombre 
                   FROM pedidos_productos pp 
                   JOIN productos p ON pp.id_producto = p.id 
                   WHERE pp.id_pedido IN ($placeholders)";
    $tiposIdsPedido = str_repeat('i', count($idsPedidos));
    $rsProds = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosPedidos, $tiposIdsPedido, ...$idsPedidos)->get_result();
	//Si la consulta ha devuelto algo, recorremos la lista de pedidos almacenando en cada uno los productos correspondientes (en el array vacío que habíamos creado antes)
    if ($rsProds && $rsProds->num_rows > 0) {
        while ($p = $rsProds->fetch_assoc()) {
            $pedidos[$p['id_pedido']]['productos'][] = $p;
        }
        $rsProds->free();
    }
}

//Coger nombre de usuario y avatar
$nombreCamarero = $_SESSION['nombreUsuario'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'default.png'; 

//Contenido principal de la página
$contenidoPrincipal = <<<EOS
    <div class="tablet-camarero-header">
        <h2>Panel Camarero</h2>
        <div class="tablet-camarero-user">
            <span><strong>{$nombreCamarero}</strong></span>
            <img src="img/avatares/{$avatarCamarero}" alt="Avatar" class="tablet-camarero-avatar">
        </div>
    </div>

    <div class="tablet-camarero-layout">
EOS;

//Genera la vista de las tarjetas en la tablet con la información necesaria
function generarTarjetaPedido($pedido, $botonTexto, $botonClase, $siguienteEstado) {
    $totalFmt = number_format($pedido['total'], 2, '.', '');
    $htmlProductos = '<div class="tablet-camarero-productos">';
    foreach ($pedido['productos'] as $prod) {
        $htmlProductos .= "<div class='tablet-camarero-producto-row'>
            <span class='tablet-camarero-producto-nombre'>{$prod['cantidad']}x {$prod['nombre']}</span>
            <span class='tablet-camarero-producto-precio'>".number_format($prod['cantidad']*$prod['precio_unitario'],2)."€</span>
        </div>";
    }
    $htmlProductos .= '</div>';
    
    return <<<HTML
    <div class="tablet-camarero-card">
        <div class="tablet-camarero-card-header">
            <strong>#{$pedido['numero_pedido']}</strong>
            <span class="tablet-camarero-card-type">{$pedido['tipo']}</span>
        </div>
        {$htmlProductos}
        <div class="tablet-camarero-total">Total: {$totalFmt}€</div>
        <form action="tablet_camarero.php" method="POST">
            <input type="hidden" name="id_pedido" value="{$pedido['id']}">
            <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
            <button type="submit" class="tablet-camarero-btn {$botonClase}">
                {$botonTexto}
            </button>
        </form>
    </div>
HTML;
}

//Recorre los pedidos con el array creado anteriormente y creamos las tarjetas de pedido dependiendo del estado del pedido
$cols = ['Recibido' => '', 'Listo cocina' => '', 'Terminado' => ''];
foreach ($pedidos as $p) {
    if ($p['estado'] === 'Recibido') $cols['Recibido'] .= generarTarjetaPedido($p, 'Cobrar', 'tablet-camarero-btn--cobrar', 'En preparacion');
    if ($p['estado'] === 'Listo cocina') $cols['Listo cocina'] .= generarTarjetaPedido($p, 'Preparar', 'tablet-camarero-btn--preparar', 'Terminado');
    if ($p['estado'] === 'Terminado') $cols['Terminado'] .= generarTarjetaPedido($p, 'Entregar', 'tablet-camarero-btn--entregar', 'Entregado');
}

//Concatenamos las columnas al contenido principal creado previamente
$contenidoPrincipal .= <<<EOS
        <div class="tablet-camarero-column tablet-camarero-column--cobros">
            <h3>Cobros</h3>
            {$cols['Recibido']}
        </div>
        <div class="tablet-camarero-column tablet-camarero-column--cocina">
            <h3>Cocina</h3>
            {$cols['Listo cocina']}
        </div>
        <div class="tablet-camarero-column tablet-camarero-column--entrega">
            <h3>Entrega</h3>
            {$cols['Terminado']}
        </div>
    </div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';