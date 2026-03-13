<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\Pedido;

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
    Pedido::cambiarEstado($idPed, $nuevoEst);
    header('Location: ' . RUTA_APP . '/tablet_camarero.php');
    exit();
}

//Título y estilos
$tituloPagina = 'Tablet Camarero';
$estilosExtra = ['tablet_camarero.css'];

//Consulta para coger los pedidos con estados recibido, listo cocina y terminado desde la base de datos
$listaPedidos = Pedido::porEstados(['Recibido', 'Listo cocina', 'Terminado']);

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
    //Obtenemos los productos asociados a cada pedido
    $detalles = Pedido::detallesPedidos($idsPedidos);
    foreach ($detalles as $idPedido => $lineas) {
        foreach ($lineas as $p) {
            $pedidos[$idPedido]['productos'][] = $p;
        }
    }
}

//Coger nombre de usuario y avatar
$nombreCamarero = $_SESSION['nombreUsuario'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'default.png'; 

$rutaImgs = RUTA_IMGS;

//Contenido principal de la página
$contenidoPrincipal = <<<EOS
    <div class="tablet-camarero-header">
        <h2>Panel Camarero</h2>
        <div class="tablet-camarero-user">
            <span><strong>{$nombreCamarero}</strong></span>
            <img src="{$rutaImgs}/avatares/{$avatarCamarero}" alt="Avatar" class="tablet-camarero-avatar">
        </div>
    </div>

    <div class="tablet-camarero-layout">
EOS;

/**
 * Genera la vista de las tarjetas en la tablet con la información necesaria
 *
 * @param array $pedido
 * @param string $botonTexto
 * @param string $botonClase
 * @param string $siguienteEstado
 * @return string
 */
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
    
    $rutaApp = RUTA_APP;

    return <<<HTML
    <div class="tablet-camarero-card">
        <div class="tablet-camarero-card-header">
            <strong>#{$pedido['numero_pedido']}</strong>
            <span class="tablet-camarero-card-type">{$pedido['tipo']}</span>
        </div>
        {$htmlProductos}
        <div class="tablet-camarero-total">Total: {$totalFmt}€</div>
        <form action="$rutaApp/tablet_camarero.php" method="POST">
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