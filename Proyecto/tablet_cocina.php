<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Pedido;

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
    Pedido::cambiarEstado($idPed, $nuevoEst);
    header('Location: ' . RUTA_APP . '/tablet_cocina.php');
    exit();
}

//Título y estilos
$tituloPagina = 'Tablet Cocina';
$estilosExtra = ['tablet_cocina.css'];

//Consulta para coger los pedidos con estados preparacion y cocinando desde la base de datos
$listaPedidos = Pedido::porEstados(['En preparacion', 'Cocinando']);

//Si la consulta anterior ha devuelto algo, recorremos los pedidos devueltos para estructurar los arrays que se usarán posteriormente
$pedidos = [];
$idsPedidos = [];
if (!empty($listaPedidos)) {
    foreach ($listaPedidos as $fila) {
		//Guardamos los pedidos con su id como clave
        $pedidos[$fila['id']] = $fila;
		//Creamos un array vacío en cada pedido para posteriormente almacenar ahí los productos
        $pedidos[$fila['id']]['productos'] = [];
		//Guardamos los ids de los pedidos
        $idsPedidos[] = $fila['id'];
    }
}
//Si hay algún pedido
if (!empty($idsPedidos)) {
    //Consulta que devuelve la información de todos los productos de todos los pedidos
    $detalles = Pedido::detallesPedidos($idsPedidos);
    foreach ($detalles as $idPedido => $lineas) {
        foreach ($lineas as $prod) {
            $pedidos[$idPedido]['productos'][] = $prod;
        }
    }
}

/**
 * Genera la vista de las tarjetas en la tablet con la información necesaria
 *
 * @param array $pedido
 * @param string $botonTexto
 * @param string $claseBoton
 * @param string $siguienteEstado
 * @return string
 */
function generarTarjetaCocina($pedido, $botonTexto, $claseBoton, $siguienteEstado) {
    $htmlProductos = "";
    foreach ($pedido['productos'] as $prod) {
        $htmlProductos .= "<div class='tablet-cocinero-producto-row'>
                                <span><strong>{$prod['cantidad']}x</strong> {$prod['nombre']}</span>
                           </div>";
    }

    $rutaApp = RUTA_APP;

    return <<<HTML
    <div class="tablet-cocinero-card">
        <div class="tablet-cocinero-card-header">
            <span>#{$pedido['numero_pedido']}</span>
            <span class="tablet-camarero-card-type">{$pedido['tipo']}</span>
        </div>
        <div class="tablet-cocinero-productos">
            $htmlProductos
        </div>
        <form action="$rutaApp/tablet_cocina.php" method="POST">
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

$rutaImgs = RUTA_IMGS;

//Contenido principal de la página
$contenidoPrincipal = <<<EOS
<div class="tablet-cocinero-header">
    <h2>Panel Cocina</h2>
    <div class="tablet-camarero-user">
        <span>Chef: <strong>{$nombreCocinero}</strong></span>
        <img src="{$rutaImgs}/avatares/{$avatar}" alt="Avatar" class="tablet-camarero-avatar">
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