<?php

namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Aplicacion;


class Oferta
{
	// Función que nos permite obtener todas las ofertas
    public static function todasLasOfertas(): array
    {
        $queryOfertas = "SELECT O.*
                                FROM ofertas O";

        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryOfertas)->get_result();
        $ofertas = [];

        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $ofertas[] = $fila;
            }
            $rs->free();
        }

        return $ofertas;
    }
	
	// Función que nos devuelve todos los productos de una oferta dada su id
	public static function obtenerProductosOferta(int $id_oferta): array 
	{
        $query = "SELECT P.nombre, OP.cantidad, P.precio_base, P.iva 
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

}
