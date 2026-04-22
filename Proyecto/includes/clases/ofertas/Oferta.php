<?php

namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Aplicacion;


class Oferta
{
    private $id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $descuento;

    public function __construct(
        string $nombre,
        ?string $descripcion,
        string $fecha_inicio,
        string $fecha_fin,
        int $descuento,
        ?int $id = null
    ) {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->descuento = $descuento;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getFechaInicio(): string { return $this->fecha_inicio; }
    public function getFechaFin(): string { return $this->fecha_fin; }
    public function getDescuento(): int { return $this->descuento; }

    private static function creaDesdeFila(array $fila): Oferta
    {
        return new Oferta(
            $fila['nombre'],
            $fila['descripcion'] ?? null,
            $fila['fecha_inicio'],
            $fila['fecha_fin'],
            (int)$fila['descuento'],
            isset($fila['id']) ? (int)$fila['id'] : null
        );
    }

	// Función que nos permite obtener todas las ofertas
    public static function todasLasOfertas(): array
    {
        $queryOfertas = "SELECT O.*
                                FROM ofertas O";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryOfertas)->get_result();
        $ofertas = [];

        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $ofertas[] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $ofertas;
    }
	
	// Función que nos devuelve todos los productos de una oferta dada su id
	public static function obtenerProductosOferta(int $id_oferta): array 
	{
        $query = "SELECT P.id, P.nombre, OP.cantidad, P.precio_base, P.iva, P.imagen 
                  FROM ofertas_productos OP
                  JOIN productos P ON OP.id_producto = P.id
                  WHERE OP.id_oferta = ?";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($query, "i", $id_oferta)->get_result();
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
     * Inserta una nueva oferta y devuelve true/false según éxito
     *
     * @param string $nombre
     * @param string|null $descripcion
     * @param string $fecha_inicio
     * @param string $fecha_fin
     * @param int $descuento
	 * @param array productos
     * @return bool
     */
    public static function crear(
        string $nombre,
        ?string $descripcion,
        string $fecha_inicio,
        string $fecha_fin,
        int $descuento,
		array $productos,
		array $cantidades
        ): bool
    {
        $queryInsertOferta = "INSERT INTO ofertas (nombre, descripcion, fecha_inicio, fecha_fin, descuento)
                                VALUES (?, ?, ?, ?, ?)";

        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryInsertOferta,
            "ssssi",
            $nombre,
            $descripcion,
            $fecha_inicio,
			$fecha_fin,
            $descuento
        );

        if ($stmt->affected_rows === 1) {
            $id_oferta = $stmt->insert_id;
            foreach ($productos as $indice => $id_producto) {
                $cantidad = isset($cantidades[$indice]) ? (int)$cantidades[$indice] : 1;
                $queryInsertProd = "INSERT INTO ofertas_productos (id_oferta, id_producto, cantidad) VALUES (?, ?, ?)";
                Aplicacion::getInstance()->ejecutarConsultaBd($queryInsertProd, "iii", $id_oferta, $id_producto, $cantidad);
            }
            
            return true;
        }
        return false;
    }
	
	/**
     * Borra una oferta definitivamente
     *
     * @param int $id
     * @return bool
     */
    public static function borrar(int $id): bool
    {
		$queryBorrarProductos = "DELETE FROM ofertas_productos WHERE id_oferta = ?";
		$stmtAsociada  = Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarProductos, "i", $id);
		if ($stmtAsociada ->affected_rows >= 0) {
			$queryBorrarOferta = "DELETE FROM ofertas WHERE id = ?";
			$stmtOferta  = Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarOferta, "i", $id);
			
			return $stmtOferta ->affected_rows === 1;
		}

        return false;
    }
	
	public static function porID(int $id): ?Oferta
	{
		$queryOfertaPorId = "SELECT * FROM ofertas WHERE id = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryOfertaPorId, "i", $id)->get_result();

        $oferta = null;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $oferta = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $oferta;
	}
	
	public static function actualizar(
		int $id,
        string $nombre,
        ?string $descripcion,
        string $fecha_inicio,
        string $fecha_fin,
        int $descuento,
		array $productos,
		array $cantidades
        ): bool
    {
        $queryInsertOferta = "UPDATE ofertas 
							  SET nombre = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, descuento = ?
                              WHERE id = ?";

        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryInsertOferta,
            "ssssii",
            $nombre,
            $descripcion,
            $fecha_inicio,
			$fecha_fin,
            $descuento,
			$id
        );

        if ($stmt !== false) {
			$queryBorrarViejos = "DELETE FROM ofertas_productos WHERE id_oferta = ?";
			$stmtBorrar = Aplicacion::getInstance()->ejecutarConsultaBd($queryBorrarViejos, "i", $id);

			foreach ($productos as $indice => $id_producto) {
				$cantidad = isset($cantidades[$indice]) ? (int)$cantidades[$indice] : 1;
				$queryInsertProd = "INSERT INTO ofertas_productos (id_oferta, id_producto, cantidad) VALUES (?, ?, ?)";
				$stmtReinsertar = Aplicacion::getInstance()->ejecutarConsultaBd($queryInsertProd, "iii", $id, $id_producto, $cantidad);
			}

			return true;
		}
    
		return false;
    }
	
	// Función que nos permite obtener todas las ofertas activas
    public static function ofertasActivas(): array
    {
        $queryOfertas = "SELECT * FROM ofertas 
						 WHERE CURRENT_DATE BETWEEN fecha_inicio AND fecha_fin";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryOfertas)->get_result();
        $ofertas = [];

        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $ofertas[] = self::creaDesdeFila($fila);
            }
            $rs->free();
        }

        return $ofertas;
    }
	
	// Función que nos permite saber si una oferta es aplicable dados unos productos en carrito
    public static function esAplicable(int $idOferta, array $carrito): bool
	{
		$productosNecesarios = self::obtenerProductosOferta($idOferta);
		$valido = true;

		foreach ($productosNecesarios as $pReq) {
			$idReq = $pReq['id'];
			$cantReq = $pReq['cantidad'];

			if (!isset($carrito[$idReq]) || $carrito[$idReq] < $cantReq) {
				$valido = false;
			}
		}

		return $valido;
	}
}