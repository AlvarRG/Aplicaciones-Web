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

	//Busca el nombre del usuario, y si la contraseña no coincide devuelve false
    public static function login($nombreUsuario, $password)
    {
        $usuario = self::buscaUsuario($nombreUsuario);
        if ($usuario && $usuario->compruebaPassword($password)) {
            return $usuario;
        }
        return false;
    }
    
	//Crea un nuevo usuario con los parámetros dados
    public static function crea($nombreUsuario, $password, $nombre, $rol)
    {
        $user = new Usuario($nombreUsuario, self::hashPassword($password), $nombre, null, $rol);
        return $user->guarda();
    }

	//Dado un nombre de usuario lo busca en la base de datos y 
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

	//Busca un usuario por id
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
    
	//Devuelve el hash de la contraseña dada
    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

	//Dado un usuario lo mete en la base de datos
    private static function inserta($usuario)
    {
		//Conexión a la base de datos
        $conn = Aplicacion::getInstance()->getConexionBd();
		//Inserta el usuario con los valores correspondientes
        $query = sprintf("INSERT INTO usuarios(nombreUsuario, nombre, password, rol) VALUES ('%s', '%s', '%s', %d)"
            , $conn->real_escape_string($usuario->nombreUsuario)
            , $conn->real_escape_string($usuario->nombre)
            , $conn->real_escape_string($usuario->password)
            , $usuario->rol
        );
		//Si ha tenido éxito devolvemos el usuario, si no false y escribimos un error
        if ($conn->query($query)) {
            $usuario->id = $conn->insert_id;
            return $usuario;
        } else {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
            return false;
        }
    }
    
	//Actualiza un usuario con sus nuevos datos asociados
    private static function actualiza($usuario)
    {
		//Conexión a la base de datos
        $conn = Aplicacion::getInstance()->getConexionBd();
		//Inserta el usuario con los valores correspondientes
        $query = sprintf("UPDATE usuarios SET nombreUsuario = '%s', nombre='%s', password='%s', rol=%d WHERE id=%d"
            , $conn->real_escape_string($usuario->nombreUsuario)
            , $conn->real_escape_string($usuario->nombre)
            , $conn->real_escape_string($usuario->password)
            , $usuario->rol
            , $usuario->id
        );
		//Si ha tenido éxito devolvemos el usuario, si no false y escribimos un error
        if ($conn->query($query)) {
            return $usuario;
        } else {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
            return false;
        }
    }
    
	//Borra el usuario que corresponde con el id dado
    private static function borraPorId($idUsuario)
    {
        if (!$idUsuario) return false;
		//Conexión a la base de datos
        $conn = Aplicacion::getInstance()->getConexionBd();
		//Elimina al usuario de la base de datos
        $query = sprintf("DELETE FROM usuarios WHERE id = %d", $idUsuario);
        return $conn->query($query);
    }

    private $id;
    private $nombreUsuario;
    private $password;
    private $nombre;
    private $rol;
    private $avatar;

	//Constructor de la clase
    private function __construct($nombreUsuario, $password, $nombre, $id = null, $rol = self::USER_ROLE, $avatar = null)
    {
        $this->id = $id;
        $this->nombreUsuario = $nombreUsuario;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->rol = $rol;
        $this->avatar = $avatar;
    }

    public function getId() { return $this->id; } //Devuelve el id
    public function getNombreUsuario() { return $this->nombreUsuario; } //Devuelve el nombre de usuario
    public function getNombre() { return $this->nombre; } //Devuelve el nombre
    public function getAvatar() { return $this->avatar ?? 'default.png'; } //Devuelve el avatar
    public function getRol() { return $this->rol; } //Devuelve el rol

	//Devuelve si el usuario tiene el rol dado o no
    public function tieneRol($role)
    {
        return $this->rol == $role;
    }

	//Devueleve si las contraseñas coinciden o no
    public function compruebaPassword($password)
    {
        return password_verify($password, $this->password);
    }
	
	//Si el usuario existe lo actualiza, si no lo inserta nuevo
    public function guarda()
    {
        if ($this->id !== null) {
            return self::actualiza($this);
        }
        return self::inserta($this);
    }
    
	//Si el usuario existe lo borra
    public function borrate()
    {
        if ($this->id !== null) {
            return self::borraPorId($this->id);
        }
        return false;
    }
}