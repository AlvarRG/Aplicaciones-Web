<?php

namespace es\ucm\fdi\aw;

class Producto
{
    /**
     * Devuelve todos los productos ofertados (ofertado = 1) junto con el nombre de su categoría
     * Se utiliza principalmente en la carta pública.
     *
     * @return array<int,array<string,mixed>>
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
                $productos[] = $fila;
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
     * @return array<int,array<string,mixed>> indexados por id de producto
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
                $productos[(int)$fila['id']] = $fila;
            }
            $rs->free();
        }

        return $productos;
    }

    /**
     * Devuelve un solo producto por id o null si no existe
     *
     * @param int $id
     * @return array|null
     */
    public static function porId(int $id): ?array
    {
        $queryProductoPorId = "SELECT * FROM productos WHERE id = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryProductoPorId, "i", $id)->get_result();

        $producto = null;
        if ($rs) {
            $producto = $rs->fetch_assoc() ?: null;
            $rs->free();
        }

        return $producto;
    }

    /**
     * Devuelve todos los productos con el nombre de su categoría
     * Pensado para panel de administración.
     *
     * @return array<int,array<string,mixed>>
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
                $productos[] = $fila;
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
