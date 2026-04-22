<?php

namespace es\ucm\fdi\aw\categorias;

use es\ucm\fdi\aw\Aplicacion;


class Categoria
{
    private $id;
    private $nombre;
    private $descripcion;
    private $imagen;

    public function __construct(string $nombre, ?string $descripcion = null, string $imagen = 'cat_default.png', ?int $id = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getImagen(): string { return $this->imagen; }

    private static function creaDesdeFila(array $fila): Categoria
    {
        return new Categoria(
            $fila['nombre'],
            $fila['descripcion'] ?? null,
            $fila['imagen'] ?? 'cat_default.png',
            isset($fila['id']) ? (int)$fila['id'] : null
        );
    }

    /**
     * Devuelve todas las categorías
     *
     * @return Categoria[]
     */
    public static function todas(): array
    {
        $queryCategorias = "SELECT * FROM categorias";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCategorias)->get_result();

        $categorias = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $categorias[] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $categorias;
    }

    /**
     * Devuelve una categoría por id o null si no existe
     *
     * @param int $id
     * @return Categoria|null
     */
    public static function porId(int $id): ?Categoria
    {
        $queryCategoriaPorId = "SELECT * FROM categorias WHERE id = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCategoriaPorId, "i", $id)->get_result();

        $categoria = null;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $categoria = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $categoria;
    }

    /**
     * Crea una nueva categoría
     *
     * @param string $nombre
     * @param string|null $descripcion
     * @param string $imagen
     * @return bool
     */
    public static function crear(string $nombre, ?string $descripcion, string $imagen): bool
    {
        $queryInsertCategoria = "INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryInsertCategoria, "sss", $nombre, $descripcion, $imagen);

        return $stmt->affected_rows === 1;
    }

    /**
     * Actualiza una categoría existente
     *
     * @param int $id
     * @param string $nombre
     * @param string|null $descripcion
     * @param string $imagen
     * @return bool
     */
    public static function actualizar(int $id, string $nombre, ?string $descripcion, string $imagen): bool
    {
        $queryUpdateCategoria = "UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateCategoria, "sssi", $nombre, $descripcion, $imagen, $id);

        return $stmt->affected_rows >= 0;
    }

    /**
     * Borra una categoría dado su id
     *
     * @param int $id
     * @return bool
     */
    public static function borrar(int $id): bool
    {
        $queryBorrarCategoria = "DELETE FROM categorias WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarCategoria, "i", $id);

        return $stmt->affected_rows >= 0;
    }
}
