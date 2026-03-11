<?php

namespace es\ucm\fdi\aw;

class Usuario
{
    //Rol cliente (id en BD = 1)
    public const USER_ROLE = 1;

    //Rol camarero (id en BD = 2)
    public const CAMARERO_ROLE = 2;

    //Rol cocinero (id en BD = 3)
    public const COCINERO_ROLE = 3;

    //Rol gerente/admin (id en BD = 4)
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
    public static function crea($nombreUsuario, $password, $nombre, $rol, $apellidos = null, $email = null)
    {
        $user = new Usuario($nombreUsuario, self::hashPassword($password), $nombre, null, $rol, null, $apellidos, $email);
        return $user->guarda();
    }

	//Dado un nombre de usuario lo busca en la base de datos y 
    public static function buscaUsuario($nombreUsuario)
    {
        $queryUsuario = "SELECT * FROM usuarios WHERE nombreUsuario = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryUsuario, "s", $nombreUsuario)->get_result();
        $result = false;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $result = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['id'], $fila['rol'], $fila['avatar'], $fila['apellidos'], $fila['email']);
            }
            $rs->free();
        }
        return $result;
    }

    //Devuelve todos los usuarios de la base de datos en un array
    public static function buscaTodos()
    {
        $queryUsuarios = "SELECT U.id, U.nombreUsuario, U.nombre, U.apellidos, U.email, R.nombre AS nombreRol 
                  FROM usuarios U 
                  JOIN roles R ON U.rol = R.id";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryUsuarios)->get_result();
        
        $usuarios = [];
        if ($rs) {
            while ($fila = $rs->fetch_assoc()) {
                $usuarios[] = $fila;
            }
            $rs->free();
        }
        return $usuarios;
    }

    //Comprueba si un nombre de usuario o email ya están en uso
    public static function compruebaDisponibilidad($nombreUsuario, $email)
    {
        $queryCheckUsuarioEmail = "SELECT id FROM usuarios WHERE nombreUsuario = ? OR email = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryCheckUsuarioEmail, "ss", $nombreUsuario, $email)->get_result();
        $disponible = true;
        if ($rs && $rs->num_rows > 0) {
            $disponible = false;
        }
        if ($rs) {
            $rs->free();
        }
        return $disponible;
    }

    //Actualiza el perfil de un usuario
    public static function actualizarPerfil($nombreUsuario, $nombre, $apellidos, $email, $avatar)
    {
        $queryUpdatePerfil = "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, avatar = ? WHERE nombreUsuario = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryUpdatePerfil,
            "sssss",
            $nombre,
            $apellidos,
            $email,
            $avatar,
            $nombreUsuario
        );
        return $stmt->affected_rows >= 0;
    }

    //Actualiza el rol de un usuario
    public static function actualizarRol($idUsuario, $nuevoRol)
    {
        $queryUpdateRolUsuario = "UPDATE usuarios SET rol = ? WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryUpdateRolUsuario, "ii", $nuevoRol, $idUsuario);
        return $stmt->affected_rows >= 0;
    }

	//Busca un usuario por id
    public static function buscaPorId($idUsuario)
    {
        $queryUsuarioPorId = "SELECT * FROM usuarios WHERE id = ?";
        $rs = Aplicacion::getInstance()->ejecutarConsultaBd($queryUsuarioPorId, "i", $idUsuario)->get_result();
        $result = false;
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $result = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['id'], $fila['rol'], $fila['avatar'], $fila['apellidos'], $fila['email']);
            }
            $rs->free();
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
        $queryInsertUsuario = "INSERT INTO usuarios(nombreUsuario, nombre, password, rol, apellidos, email) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryInsertUsuario,
            "sssiss",
            $usuario->nombreUsuario,
            $usuario->nombre,
            $usuario->password,
            $usuario->rol,
            $usuario->apellidos,
            $usuario->email
        );

        if ($stmt->affected_rows !== 1) {
            return false;
        }

        $usuario->id = Aplicacion::getInstance()->getConexionBd()->insert_id;
        return $usuario;
    }
    
	//Actualiza un usuario con sus nuevos datos asociados
    private static function actualiza($usuario)
    {
        $queryUpdateUsuario = "UPDATE usuarios SET nombreUsuario = ?, nombre = ?, password = ?, rol = ?, apellidos = ?, email = ? WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd(
            $queryUpdateUsuario,
            "sssissi",
            $usuario->nombreUsuario,
            $usuario->nombre,
            $usuario->password,
            $usuario->rol,
            $usuario->apellidos,
            $usuario->email,
            $usuario->id
        );

        if ($stmt->affected_rows < 0) {
            return false;
        }

        return $usuario;
    }
    
	//Borra el usuario que corresponde con el id dado
    public static function borraPorId($idUsuario)
    {
        if (!$idUsuario) return false;
        $queryDeleteUsuario = "DELETE FROM usuarios WHERE id = ?";
        $stmt = Aplicacion::getInstance()->ejecutarConsultaBd($queryDeleteUsuario, "i", $idUsuario);
        return $stmt->affected_rows === 1;
    }

    private $id;
    private $nombreUsuario;
    private $password;
    private $nombre;
    private $rol;
    private $avatar;
    private $apellidos;
    private $email;

	//Constructor de la clase
    private function __construct($nombreUsuario, $password, $nombre, $id = null, $rol = self::USER_ROLE, $avatar = null, $apellidos = null, $email = null)
    {
        $this->id = $id;
        $this->nombreUsuario = $nombreUsuario;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->rol = $rol;
        $this->avatar = $avatar;
        $this->apellidos = $apellidos;
        $this->email = $email;
    }

    public function getId() { return $this->id; } //Devuelve el id
    public function getNombreUsuario() { return $this->nombreUsuario; } //Devuelve el nombre de usuario
    public function getNombre() { return $this->nombre; } //Devuelve el nombre
    public function getAvatar() { return $this->avatar ?? 'default.png'; } //Devuelve el avatar
    public function getRol() { return $this->rol; } //Devuelve el rol
    public function getApellidos() { return $this->apellidos; } //Devuelve apellidos
    public function getEmail() { return $this->email; } //Devuelve email

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
}