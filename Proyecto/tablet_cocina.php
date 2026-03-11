<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Aplicacion;

//Si no hay sesión iniciada te lleva al login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

//Variables para distinguir las sesiones
$esCocinero = $_SESSION['esCocinero'] ?? false;
$esAdmin = $_SESSION['esAdmin'] ?? false;

//Si no eres cocinero ni admin te manda al inicio
if (!$esCocinero && !$esAdmin) {
    header('Location: index.php');
    exit();
}

//Gestionar cambio de estado en los pedidos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $idPed = (int)$_POST['id_pedido'];
    $nuevoEst = (string)$_POST['nuevo_estado'];
    $queryUpdateEstadoPedido = "UPDATE pedidos SET estado = ? WHERE id = ?";
    Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateEstadoPedido, "si", $nuevoEst, $idPed);
    header('Location: tablet_cocina.php');
    exit();
}

//Título y estilos
$tituloPagina = 'Tablet Cocina';
$estilosExtra = ['tablet_cocina.css'];

//Consulta para coger los pedidos con estados preparacion y cocinando desde la base de datos
$queryPedidos = "SELECT id, numero_pedido, tipo, estado FROM pedidos 
                 WHERE estado IN ('En preparacion', 'Cocinando') ORDER BY fecha ASC";
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
	$queryProductosPedidos = "SELECT pp.id_pedido, pp.cantidad, p.nombre 
				   FROM pedidos_productos pp 
				   JOIN productos p ON pp.id_producto = p.id 
				   WHERE pp.id_pedido IN ($placeholders)";
    $tiposIdsPedido = str_repeat('i', count($idsPedidos));
	$rsProds = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosPedidos, $tiposIdsPedido, ...$idsPedidos)->get_result();
	//Si la consulta ha devuelto algo, recorremos la lista de pedidos almacenando en cada uno los productos correspondientes (en el array vacío que habíamos creado antes)
	if ($rsProds && $rsProds->num_rows > 0) {
		while ($prod = $rsProds->fetch_assoc()) {
			$pedidos[$prod['id_pedido']]['productos'][] = $prod;
		}
        $rsProds->free();
	}
}

//Genera la vista de las tarjetas en la tablet con la información necesaria
function generarTarjetaCocina($pedido, $botonTexto, $claseBoton, $siguienteEstado) {
    $htmlProductos = "";
    foreach ($pedido['productos'] as $prod) {
        $htmlProductos .= "<div class='tablet-cocinero-producto-row'>
                                <span><strong>{$prod['cantidad']}x</strong> {$prod['nombre']}</span>
                           </div>";
    }

    return <<<HTML
    <div class="tablet-cocinero-card">
        <div class="tablet-cocinero-card-header">
            <span>#{$pedido['numero_pedido']}</span>
            <span class="tablet-camarero-card-type">{$pedido['tipo']}</span>
        </div>
        <div class="tablet-cocinero-productos">
            $htmlProductos
        </div>
        <form action="tablet_cocina.php" method="POST">
            <input type="hidden" name="id_pedido" value="{$pedido['id']}">
            <input type="hidden" name="nuevo_estado" value="{$siguienteEstado}">
            <button type="submit" class="tablet-cocinero-btn {$claseBoton}">
                $botonTexto
            </button>
        </form>
    </div>
HTML;
}

$colNuevas = "";
$colProceso = "";

//Recorre los pedidos con el array creado anteriormente y creamos las tarjetas de cocina dependiendo del estado del pedido
foreach ($pedidos as $p) {
    if ($p['estado'] === 'En preparacion') {
        $colNuevas .= generarTarjetaCocina($p, 'COCINAR', 'tablet-cocinero-btn--cocinar', 'Cocinando');
    } elseif ($p['estado'] === 'Cocinando') {
        $colProceso .= generarTarjetaCocina($p, 'LISTO', 'tablet-cocinero-btn--listo', 'Listo cocina');
    }
}

//Coger nombre de usuario y avatar
$nombreCocinero = $_SESSION['nombreUsuario'] ?? 'Chef';
$avatar = $_SESSION['avatar'] ?? 'default.png';

//Contenido principal de la página
$contenidoPrincipal = <<<EOS
<div class="tablet-cocinero-header">
    <h2>Panel Cocina</h2>
    <div class="tablet-camarero-user">
        <span>Chef: <strong>{$nombreCocinero}</strong></span>
        <img src="img/avatares/{$avatar}" alt="Avatar" class="tablet-camarero-avatar">
    </div>
</div>

<div class="tablet-cocinero-layout">
    <div class="tablet-cocinero-column tablet-cocinero-column--nuevas">
        <h3>Nuevas Comandas</h3>
        $colNuevas
    </div>
    <div class="tablet-cocinero-column tablet-cocinero-column--proceso">
        <h3>En los Fuegos</h3>
        $colProceso
    </div>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';