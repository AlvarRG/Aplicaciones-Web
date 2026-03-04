<?php

namespace es\ucm\fdi\aw;

class Usuario
{
    /** Rol cliente (id en BD = 1) */
    public const USER_ROLE = 1;

    /** Rol camarero (id en BD = 2) */
    public const CAMARERO_ROLE = 2;

    /** Rol cocinero (id en BD = 3) */
    public const COCINERO_ROLE = 3;

    /** Rol gerente/admin (id en BD = 4) */
    public const ADMIN_ROLE = 4;

    public static function login($nombreUsuario, $password)
    {
        $usuario = self::buscaUsuario($nombreUsuario);
        if ($usuario && $usuario->compruebaPassword($password)) {
            return $usuario; // Ya no hace falta cargaRoles() por separado
        }
        return false;
    }
    
    public static function crea($nombreUsuario, $password, $nombre, $rol)
    {
        $user = new Usuario($nombreUsuario, self::hashPassword($password), $nombre, null, $rol);
        return $user->guarda();
    }

    public static function buscaUsuario($nombreUsuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("SELECT * FROM usuarios WHERE nombreUsuario='%s'", $conn->real_escape_string($nombreUsuario));
        $rs = $conn->query($query);
        $result = false;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $result = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['id'], $fila['rol'], $fila['avatar']);
            }
            $rs->free();
        } else {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
        }
        return $result;
    }

    public static function buscaPorId($idUsuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("SELECT * FROM usuarios WHERE id=%d", $idUsuario);
        $rs = $conn->query($query);
        $result = false;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $result = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['id'], $fila['rol'], $fila['avatar']);
            }
            $rs->free();
        } else {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
        }
        return $result;
    }
    
    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private static function inserta($usuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("INSERT INTO usuarios(nombreUsuario, nombre, password, rol) VALUES ('%s', '%s', '%s', %d)"
            , $conn->real_escape_string($usuario->nombreUsuario)
            , $conn->real_escape_string($usuario->nombre)
            , $conn->real_escape_string($usuario->password)
            , $usuario->rol
        );
        if ($conn->query($query)) {
            $usuario->id = $conn->insert_id;
            return $usuario;
        } else {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
            return false;
        }
    }
    
    private static function actualiza($usuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("UPDATE usuarios SET nombreUsuario = '%s', nombre='%s', password='%s', rol=%d WHERE id=%d"
            , $conn->real_escape_string($usuario->nombreUsuario)
            , $conn->real_escape_string($usuario->nombre)
            , $conn->real_escape_string($usuario->password)
            , $usuario->rol
            , $usuario->id
        );
        if ($conn->query($query)) {
            return $usuario;
        } else {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
            return false;
        }
    }
    
    private static function borraPorId($idUsuario)
    {
        if (!$idUsuario) return false;
        $conn = Aplicacion::getInstance()->getConexionBd();
        $query = sprintf("DELETE FROM usuarios WHERE id = %d", $idUsuario);
        return $conn->query($query);
    }

    private $id;
    private $nombreUsuario;
    private $password;
    private $nombre;
    private $rol; // Ahora es un entero único
    private $avatar;

    private function __construct($nombreUsuario, $password, $nombre, $id = null, $rol = self::USER_ROLE, $avatar = null)
    {
        $this->id = $id;
        $this->nombreUsuario = $nombreUsuario;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->rol = $rol;
        $this->avatar = $avatar;
    }

    public function getId() { return $this->id; }
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function getNombre() { return $this->nombre; }
    public function getAvatar() { return $this->avatar ?? 'default.png'; }
    public function getRol() { return $this->rol; }

    public function tieneRol($role)
    {
        return $this->rol == $role;
    }

    public function compruebaPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function guarda()
    {
        if ($this->id !== null) {
            return self::actualiza($this);
        }
        return self::inserta($this);
    }
    
    public function borrate()
    {
        if ($this->id !== null) {
            return self::borraPorId($this->id);
        }
        return false;
    }
}