<?php

namespace es\ucm\fdi\aw;

class Pedido
{
    /**
     * Devuelve todos los pedidos de un usuario
     *
     * @param int $idUsuario
     * @return array<int,array<string,mixed>>
     */
    public static function porUsuario(int $idUsuario): array
    {
        $queryPedidosUsuario = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidosUsuario, "i", $idUsuario)->get_result();

        $pedidos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $pedidos[] = $fila;
            }
            $rs->free();
        }
        return $pedidos;
    }

    /**
     * Devuelve los pedidos activos para un usuario, según una lista fija de estados activos
     *
     * @param int $idUsuario
     * @return array
     */
    public static function activosPorUsuario(int $idUsuario): array
    {
        $estadosActivos = ['En preparacion', 'Cocinando', 'Listo cocina', 'Terminado'];
        return self::porUsuarioYEstados($idUsuario, $estadosActivos, true);
    }

    /**
     * Devuelve el historial (no activos) de un usuario
     *
     * @param int $idUsuario
     * @return array
     */
    public static function historialPorUsuario(int $idUsuario): array
    {
        $estadosActivos = ['En preparacion', 'Cocinando', 'Listo cocina', 'Terminado'];
        
        //Devuelve los pedidos que no están en la lista de estados activos
        return self::porUsuarioYEstados($idUsuario, $estadosActivos, false);
    }

    /**
     * Devuelve pedidos filtrados por estados (sin filtrar por usuario)
     *
     * @param array $estados
     * @return array
     */
    public static function porEstados(array $estados): array
    {
        if (empty($estados)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $tipos = str_repeat('s', count($estados));

        $queryPedidos = "SELECT * FROM pedidos
                         WHERE estado IN ($placeholders)
                         ORDER BY fecha ASC";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidos, $tipos, ...$estados)->get_result();

        $pedidos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $pedidos[] = $fila;
            }
            $rs->free();
        }
        return $pedidos;
    }

    /**
     * Devuelve pedidos con datos del cliente para gestión global
     *
     * @return array
     */
    public static function todosConCliente(): array
    {
        $queryPedidosGestion = "SELECT p.id, p.numero_pedido, p.fecha, p.estado, p.tipo, p.total, u.nombre AS nombre_cliente
                                FROM pedidos p
                                JOIN usuarios u ON p.id_usuario = u.id
                                ORDER BY p.fecha DESC";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidosGestion)->get_result();

        $pedidos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $pedidos[] = $fila;
            }
            $rs->free();
        }
        return $pedidos;
    }

    /**
     * Devuelve las líneas de pedido (pedidos_productos + productos) agrupadas por id_pedido
     *
     * @param int[] $idsPedidos
     * @return array<int,array<int,array<string,mixed>>>  [id_pedido => [lineas...]]
     */
    public static function detallesPedidos(array $idsPedidos): array
    {
        if (empty($idsPedidos)) {
            return [];
        }
        $idsPedidos = array_values(array_map('intval', $idsPedidos));
        $placeholders = implode(',', array_fill(0, count($idsPedidos), '?'));
        $tipos = str_repeat('i', count($idsPedidos));

        $queryProductosPedidos = "SELECT pp.id_pedido, pp.id_producto, pp.cantidad, pp.precio_unitario, pp.iva, p.nombre, p.imagen
                                  FROM pedidos_productos pp
                                  JOIN productos p ON pp.id_producto = p.id
                                  WHERE pp.id_pedido IN ($placeholders)";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosPedidos, $tipos, ...$idsPedidos)->get_result();

        $detalles = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $idPedido = (int)$fila['id_pedido'];
                if (!isset($detalles[$idPedido])) {
                    $detalles[$idPedido] = [];
                }
                $detalles[$idPedido][] = $fila;
            }
            $rs->free();
        }
        return $detalles;
    }

    /**
     * Devuelve un pedido por id asegurando que pertenece a un usuario concreto
     *
     * @param int $idPedido
     * @param int $idUsuario
     * @return array|null
     */
    public static function porIdYUsuario(int $idPedido, int $idUsuario): ?array
    {
        $queryPedidoConfirmacion = "SELECT * FROM pedidos WHERE id = ? AND id_usuario = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidoConfirmacion, "ii", $idPedido, $idUsuario)->get_result();

        $pedido = null;
        if ($rs) {
            $pedido = $rs->fetch_assoc() ?: null;
            $rs->free();
        }
        return $pedido;
    }

    /**
     * Cambia el estado de un pedido, opcionalmente restringiendo por usuario
     *
     * @param int $idPedido
     * @param string $nuevoEstado
     * @param int|null $idUsuario
     * @return bool
     */
    public static function cambiarEstado(int $idPedido, string $nuevoEstado, ?int $idUsuario = null): bool
    {
        if ($idUsuario !== null) {
            $queryUpdateEstado = "UPDATE pedidos SET estado = ? WHERE id = ? AND id_usuario = ?";
            $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateEstado, "sii", $nuevoEstado, $idPedido, $idUsuario);
        } else {
            $queryUpdateEstado = "UPDATE pedidos SET estado = ? WHERE id = ?";
            $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateEstado, "si", $nuevoEstado, $idPedido);
        }

        return $stmt->affected_rows >= 0;
    }

    /**
     * Lógica específica de cancelación por parte del cliente
     *
     * @param int $idPedido
     * @param int $idUsuario
     * @return bool
     */
    public static function cancelarCliente(int $idPedido, int $idUsuario): bool
    {
        $queryCheckEstado = "SELECT estado FROM pedidos WHERE id = ? AND id_usuario = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCheckEstado, "ii", $idPedido, $idUsuario)->get_result();

        $esCancelable = false;
        if ($rs && $rs->num_rows > 0) {
            $estadoActual = $rs->fetch_assoc()['estado'] ?? '';
            if ($estadoActual === 'Recibido') {
                $esCancelable = true;
            }
            $rs->free();
        }

        if (!$esCancelable) {
            return false;
        }

        return self::cambiarEstado($idPedido, 'Cancelado', $idUsuario);
    }

    /**
     * Crea un pedido y sus líneas en base a las líneas de carrito
     *
     * @param int    $idUsuario
     * @param string $tipoPedido  'Local' o 'Llevar'
     * @param string $metodoPago  'tarjeta' o 'camarero'
     * @param array<int,array<string,mixed>> $lineas  Cada línea: ['id' => id_producto, 'cantidad', 'precio_unitario', 'iva']
     * @return int|null id del nuevo pedido o null en caso de error
     */
    public static function crearConLineas(int $idUsuario, string $tipoPedido, string $metodoPago, array $lineas): ?int
    {
        if (empty($lineas)) {
            return null;
        }

        $totalPedido = 0.0;
        foreach ($lineas as $lin) {
            $precioUdConIva = Producto::calcularPrecioConIva((float)$lin['precio_unitario'], (int)$lin['iva']);
            $totalPedido += $precioUdConIva * (int)$lin['cantidad'];
        }

        $estadoInicial = ($metodoPago === 'tarjeta') ? 'En preparacion' : 'Recibido';

        //Nuevo número de pedido diario
        $queryNuevoNumeroPedido = "SELECT IFNULL(MAX(numero_pedido), 0) + 1 AS nuevo_num
                                   FROM pedidos
                                   WHERE DATE(fecha) = CURDATE()";
        $rsNum = Aplicacion::getInstance()->ejecutarConsultaBd($queryNuevoNumeroPedido)->get_result();
        $filaNum = $rsNum ? $rsNum->fetch_assoc() : null;
        $numeroPedidoDiario = $filaNum['nuevo_num'] ?? 1;
        if ($rsNum) {
            $rsNum->free();
        }

        //Insertar pedido
        $queryInsertPedido = "INSERT INTO pedidos (id_usuario, numero_pedido, estado, tipo, total)
                              VALUES (?, ?, ?, ?, ?)";
        Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryInsertPedido,
            "iissd",
            $idUsuario,
            (int)$numeroPedidoDiario,
            $estadoInicial,
            $tipoPedido,
            $totalPedido
        );

        $idNuevoPedido = Aplicacion::getInstance()->getConexionBd()->insert_id;
        if (!$idNuevoPedido) {
            return null;
        }

        //Insertar líneas
        $queryInsertDetalle = "INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad, precio_unitario, iva)
                               VALUES (?, ?, ?, ?, ?)";

        foreach ($lineas as $lin) {
            Aplicacion::getInstance()->ejecutarConsultaBd(
                $queryInsertDetalle,
                "iiidi",
                (int)$idNuevoPedido,
                (int)$lin['id'],
                (int)$lin['cantidad'],
                (float)$lin['precio_unitario'],
                (int)$lin['iva']
            );
        }

        return (int)$idNuevoPedido;
    }

    /**
     * Helper interno: pedidos de un usuario filtrando por un conjunto de estados (IN)
     *
     * @param int   $idUsuario
     * @param array<int,string> $estados
     * @param bool  $in  true para IN, false para NOT IN
     * @return array<int,array<string,mixed>>
     */
    private static function porUsuarioYEstados(int $idUsuario, array $estados, bool $in): array
    {
        if (empty($estados)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $tipos = 'i' . str_repeat('s', count($estados));

        $operador = $in ? 'IN' : 'NOT IN';
        $query = "SELECT * FROM pedidos
                  WHERE id_usuario = ?
                  AND estado $operador ($placeholders)
                  ORDER BY fecha DESC";

        $params = array_merge([$idUsuario], $estados);

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($query, $tipos, ...$params)->get_result();

        $pedidos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $pedidos[] = $fila;
            }
            $rs->free();
        }

        return $pedidos;
    }
}

