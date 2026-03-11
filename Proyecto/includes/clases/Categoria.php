<?php

namespace es\ucm\fdi\aw;

class Categoria
{
    //Devuelve todas las categorías
    public static function todas(): array
    {
        $queryCategorias = "SELECT * FROM categorias";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCategorias)->get_result();

        $categorias = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $categorias[] = $fila;
            }
            $rs->free();
        }

        return $categorias;
    }

    //Devuelve una categoría por id o null si no existe.
    public static function porId(int $id): ?array
    {
        $queryCategoriaPorId = "SELECT * FROM categorias WHERE id = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCategoriaPorId, "i", $id)->get_result();

        $categoria = null;
        if ($rs) {
            $categoria = $rs->fetch_assoc() ?: null;
            $rs->free();
        }

        return $categoria;
    }

    //Crea una nueva categoría
    public static function crear(string $nombre, ?string $descripcion, string $imagen): bool
    {
        $queryInsertCategoria = "INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryInsertCategoria, "sss", $nombre, $descripcion, $imagen);

        return $stmt->affected_rows === 1;
    }

    //Actualiza una categoría existente
    public static function actualizar(int $id, string $nombre, ?string $descripcion, string $imagen): bool
    {
        $queryUpdateCategoria = "UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateCategoria, "sssi", $nombre, $descripcion, $imagen, $id);

        return $stmt->affected_rows >= 0;
    }

    //Borra una categoría dado su id
    public static function borrar(int $id): bool
    {
        $queryBorrarCategoria = "DELETE FROM categorias WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarCategoria, "i", $id);

        return $stmt->affected_rows >= 0;
    }
}
