<?php

namespace es\ucm\fdi\aw\pedidos;

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;



class Pedido
{
    private $id;
    private $id_usuario;
    private $id_cocinero;
    private $numero_pedido;
    private $estado;
    private $tipo;
    private $total;
    private $fecha;
    private $nombre_cliente;
    private $avatar_cocinero;
    private $productos = [];

    public function __construct(
        int $id_usuario,
        int $numero_pedido,
        string $estado,
        string $tipo,
        float $total,
        string $fecha,
        ?int $id_cocinero = null,
        ?int $id = null,
        ?string $nombre_cliente = null,
        ?string $avatar_cocinero = null
    ) {
        $this->id_usuario = $id_usuario;
        $this->numero_pedido = $numero_pedido;
        $this->estado = $estado;
        $this->tipo = $tipo;
        $this->total = $total;
        $this->fecha = $fecha;
        $this->id_cocinero = $id_cocinero;
        $this->id = $id;
        $this->nombre_cliente = $nombre_cliente;
        $this->avatar_cocinero = $avatar_cocinero;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getIdUsuario(): int
    {
        return $this->id_usuario;
    }
    public function getIdCocinero(): ?int
    {
        return $this->id_cocinero;
    }
    public function getNumeroPedido(): int
    {
        return $this->numero_pedido;
    }
    public function getEstado(): string
    {
        return $this->estado;
    }
    public function getTipo(): string
    {
        return $this->tipo;
    }
    public function getTotal(): float
    {
        return (float) $this->total;
    }
    public function getFecha(): string
    {
        return $this->fecha;
    }
    public function getNombreCliente(): ?string
    {
        return $this->nombre_cliente;
    }
    public function getAvatarCocinero(): ?string
    {
        return $this->avatar_cocinero;
    }

    public function getProductos(): array
    {
        return $this->productos;
    }
    public function setProductos(array $productos): void
    {
        $this->productos = $productos;
    }
    public function addProducto(array $producto): void
    {
        $this->productos[] = $producto;
    }

    private static function creaDesdeFila(array $fila): Pedido
    {
        return new Pedido(
            (int) ($fila['id_usuario'] ?? 0),
            (int) ($fila['numero_pedido'] ?? 0),
            $fila['estado'] ?? '',
            $fila['tipo'] ?? '',
            (float) ($fila['total'] ?? 0),
            $fila['fecha'] ?? '',
            isset($fila['id_cocinero']) ? (int) $fila['id_cocinero'] : null,
            isset($fila['id']) ? (int) $fila['id'] : null,
            $fila['nombre_cliente'] ?? null,
            $fila['avatar_cocinero'] ?? null
        );
    }

    /**
     * Devuelve todos los pedidos de un usuario
     *
     * @param int $idUsuario
     * @return Pedido[]
     */
    public static function porUsuario(int $idUsuario): array
    {
        $queryPedidosUsuario = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidosUsuario, "i", $idUsuario)->get_result();

        $pedidos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $pedidos[] = self::creaDesdeFila($fila);
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
                $pedidos[] = self::creaDesdeFila($fila);
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
        $queryPedidosGestion = "SELECT p.*, u.nombre AS nombre_cliente, c.avatar AS avatar_cocinero
                                FROM pedidos p
                                JOIN usuarios u ON p.id_usuario = u.id
                                LEFT JOIN usuarios c ON p.id_cocinero = c.id
                                ORDER BY p.fecha DESC";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidosGestion)->get_result();

        $pedidos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $pedidos[] = self::creaDesdeFila($fila);
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

        $queryProductosPedidos = "SELECT pp.id_pedido, pp.id_producto, pp.cantidad, pp.precio_unitario, pp.iva, pp.estado, p.nombre, p.imagen
                                  FROM pedidos_productos pp
                                  JOIN productos p ON pp.id_producto = p.id
                                  WHERE pp.id_pedido IN ($placeholders)";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosPedidos, $tipos, ...$idsPedidos)->get_result();

        $detalles = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $idPedido = (int) $fila['id_pedido'];
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
    public static function porIdYUsuario(int $idPedido, int $idUsuario): ?Pedido
    {
        $queryPedidoConfirmacion = "SELECT * FROM pedidos WHERE id = ? AND id_usuario = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryPedidoConfirmacion, "ii", $idPedido, $idUsuario)->get_result();

        $pedido = null;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $pedido = self::creaDesdeFila($fila);
            }
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
     * Asigna un cocinero a un pedido y actualiza su estado, y el estado de todos sus productos
     */
    public static function asignarCocineroYEstado(int $idPedido, int $idCocinero, string $nuevoEstado): bool
    {
        $queryUpdatePedido = "UPDATE pedidos SET estado = ?, id_cocinero = ? WHERE id = ?";
        Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdatePedido, "sii", $nuevoEstado, $idCocinero, $idPedido);

        $queryUpdateProductos = "UPDATE pedidos_productos SET estado = ? WHERE id_pedido = ?";
        Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateProductos, "si", $nuevoEstado, $idPedido);

        return true;
    }

    /**
     * Cambia el estado de un producto concreto dentro de un pedido
     */
    public static function cambiarEstadoProducto(int $idPedido, int $idProducto, string $nuevoEstado): bool
    {
        $query = "UPDATE pedidos_productos SET estado = ? WHERE id_pedido = ? AND id_producto = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($query, "sii", $nuevoEstado, $idPedido, $idProducto);

        // Comprobar si todos los productos están ya listos o más avanzados
        if ($nuevoEstado === 'Listo cocina' || $nuevoEstado === 'Terminado') {
            $queryCheck = "SELECT COUNT(*) as pendientes FROM pedidos_productos WHERE id_pedido = ? AND estado NOT IN ('Listo cocina', 'Terminado', 'Entregado')";
            $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCheck, "i", $idPedido)->get_result();
            if ($rs) {
                $fila = $rs->fetch_assoc();
                if ((int) $fila['pendientes'] === 0) {
                    // Todos listos, avanzamos el pedido general
                    self::cambiarEstado($idPedido, 'Listo cocina');
                }
                $rs->free();
            }
        }
        return $stmt->affected_rows >= 0;
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
            $precioUdConIva = Producto::calcularPrecioConIva((float) $lin['precio_unitario'], (int) $lin['iva']);
            $totalPedido += $precioUdConIva * (int) $lin['cantidad'];
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
            (int) $numeroPedidoDiario,
            $estadoInicial,
            $tipoPedido,
            $totalPedido
        );

        $idNuevoPedido = Aplicacion::getInstance()->getConexionBd()->insert_id;
        if (!$idNuevoPedido) {
            return null;
        }

        //Insertar líneas
        $queryInsertDetalle = "INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad, precio_unitario, iva, estado)
                               VALUES (?, ?, ?, ?, ?, ?)";

        foreach ($lineas as $lin) {
            Aplicacion::getInstance()->ejecutarConsultaBd(
                $queryInsertDetalle,
                "iiidis",
                (int) $idNuevoPedido,
                (int) $lin['id'],
                (int) $lin['cantidad'],
                (float) $lin['precio_unitario'],
                (int) $lin['iva'],
                $estadoInicial
            );
        }

        return (int) $idNuevoPedido;
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
                $pedidos[] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $pedidos;
    }
}

