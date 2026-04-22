<?php

namespace es\ucm\fdi\aw\productos;

use es\ucm\fdi\aw\Aplicacion;


class Producto
{
    private $id;
    private $id_categoria;
    private $nombre;
    private $descripcion;
    private $precio_base;
    private $iva;
    private $disponible;
    private $ofertado;
    private $imagen;
    private $nombre_cat;

    public function __construct(
        int $id_categoria,
        string $nombre,
        ?string $descripcion,
        float $precio_base,
        int $iva,
        int $disponible,
        int $ofertado,
        string $imagen,
        ?int $id = null,
        ?string $nombre_cat = null
    ) {
        $this->id = $id;
        $this->id_categoria = $id_categoria;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio_base = $precio_base;
        $this->iva = $iva;
        $this->disponible = $disponible;
        $this->ofertado = $ofertado;
        $this->imagen = $imagen;
        $this->nombre_cat = $nombre_cat;
    }

    public function getId(): ?int { return $this->id; }
    public function getIdCategoria(): int { return $this->id_categoria; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getPrecioBase(): float { return (float)$this->precio_base; }
    public function getIva(): int { return (int)$this->iva; }
    public function getDisponible(): int { return (int)$this->disponible; }
    public function getOfertado(): int { return (int)$this->ofertado; }
    public function getImagen(): string { return $this->imagen; }
    public function getNombreCategoria(): ?string { return $this->nombre_cat; }

    public function getPrecioConIva(): float 
    {
        return self::calcularPrecioConIva((float)$this->precio_base, (int)$this->iva);
    }

    private static function creaDesdeFila(array $fila): Producto
    {
        return new Producto(
            (int)$fila['id_categoria'],
            $fila['nombre'],
            $fila['descripcion'] ?? null,
            (float)$fila['precio_base'],
            (int)$fila['iva'],
            (int)$fila['disponible'],
            (int)$fila['ofertado'],
            $fila['imagen'] ?? 'prod_default.png',
            isset($fila['id']) ? (int)$fila['id'] : null,
            $fila['nombre_cat'] ?? null
        );
    }

    /**
     * Devuelve todos los productos ofertados (ofertado = 1) junto con el nombre de su categoría
     * Se utiliza principalmente en la carta pública.
     *
     * @return Producto[]
     */
    public static function todosOfertados(): array
    {
        $queryProductosCarta = "SELECT P.*, C.nombre AS nombre_cat
                                FROM productos P
                                JOIN categorias C ON P.id_categoria = C.id
                                WHERE P.ofertado = 1
                                ORDER BY C.nombre, P.nombre";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosCarta)->get_result();
        $productos = [];

        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $productos[] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $productos;
    }

    /**
     * Devuelve todos los productos correspondientes a la lista de ids dada
     * Se utiliza en el carrito y en el pago.
     *
     * @param int[] $ids
     * @return Producto[] indexados por id de producto
     */
    public static function porIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $ids = array_values(array_map('intval', $ids));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $tipos = str_repeat('i', count($ids));

        $queryProductosCarrito = "SELECT * FROM productos WHERE id IN ($placeholders)";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosCarrito, $tipos, ...$ids)->get_result();

        $productos = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $productos[(int)$fila['id']] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $productos;
    }

    /**
     * Devuelve un solo producto por id o null si no existe
     *
     * @param int $id
     * @return Producto|null
     */
    public static function porId(int $id): ?Producto
    {
        $queryProductoPorId = "SELECT * FROM productos WHERE id = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductoPorId, "i", $id)->get_result();

        $producto = null;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $producto = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $producto;
    }

    /**
     * Devuelve todos los productos con el nombre de su categoría
     * Pensado para panel de administración.
     *
     * @return Producto[]
     */
    public static function todosConCategoria(): array
    {
        $queryProductosAdmin = "SELECT P.*, C.nombre AS nombre_cat
                                FROM productos P
                                JOIN categorias C ON P.id_categoria = C.id";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductosAdmin)->get_result();
        $productos = [];

        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $productos[] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $productos;
    }

    /**
     * Inserta un nuevo producto y devuelve true/false según éxito
     *
     * @param int $idCategoria
     * @param string $nombre
     * @param string|null $descripcion
     * @param float $precioBase
     * @param int $iva
     * @param int $disponible
     * @param int $ofertado
     * @param string $imagen
     * @return bool
     */
    public static function crear(
        int $idCategoria,
        string $nombre,
        ?string $descripcion,
        float $precioBase,
        int $iva,
        int $disponible,
        int $ofertado,
        string $imagen
        ): bool
    {
        $queryInsertProducto = "INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado, imagen)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryInsertProducto,
            "issdiiis",
            $idCategoria,
            $nombre,
            $descripcion,
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $imagen
        );

        return $stmt->affected_rows === 1;
    }

    /**
     * Actualiza un producto existente
     *
     * @param int $id
     * @param int $idCategoria
     * @param string $nombre
     * @param string|null $descripcion
     * @param float $precioBase
     * @param int $iva
     * @param int $disponible
     * @param int $ofertado
     * @param string $imagen
     * @return bool
     */
    public static function actualizar(
        int $id,
        int $idCategoria,
        string $nombre,
        ?string $descripcion,
        float $precioBase,
        int $iva,
        int $disponible,
        int $ofertado,
        string $imagen
        ): bool
    {
        $queryUpdateProducto = "UPDATE productos
                                SET nombre = ?, id_categoria = ?, descripcion = ?, precio_base = ?, iva = ?, disponible = ?, ofertado = ?, imagen = ?
                                WHERE id = ?";

        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryUpdateProducto,
            "sisdiiisi",
            $nombre,
            $idCategoria,
            $descripcion,
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $imagen,
            $id
        );

        return $stmt->affected_rows >= 0;
    }

    /**
     * Marca un producto como no ofertado y no disponible (retirado de la carta)
     *
     * @param int $id
     * @return bool
     */
    public static function retirarDeCarta(int $id): bool
    {
        $queryRetirarProducto = "UPDATE productos SET ofertado = 0, disponible = 0 WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryRetirarProducto, "i", $id);

        return $stmt->affected_rows >= 0;
    }

    /**
     * Borra un producto definitivamente
     *
     * @param int $id
     * @return bool
     */
    public static function borrar(int $id): bool
    {
        $queryBorrarProducto = "DELETE FROM productos WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarProducto, "i", $id);

        return $stmt->affected_rows >= 0;
    }

    /**
     * Helper de cálculo de precio final con IVA
     *
     * @param float $precioBase
     * @param int $iva
     * @return float
     */
    public static function calcularPrecioConIva(float $precioBase, int $iva): float
    {
        return $precioBase * (1 + $iva / 100);
    }
}
