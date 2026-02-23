<?php

function generaErroresGlobalesFormulario($errores)
{
	$html = '';
	$keys = array_filter(array_keys($errores), function($v) {
		return is_numeric($v);
	});
	if (count($keys) > 0) {
		$html = '<ul class="errores">';
		foreach($keys as $key) {
			$html .= "<li>{$errores[$key]}</li>";
		}
		$html .= '</ul>';
	}
	return $html;
}

function generarError($campo, $errores)
{
    return isset($errores[$campo]) ? "<span class=\"form-field-error\">{$errores[$campo]}</span>": '';
}

function generaErroresCampos($campos, $errores) {
    $erroresCampos = [];
    foreach($campos as $campo) {
        $erroresCampos[$campo] = generarError($campo, $errores);
    }
    return $erroresCampos;
}

function conexionBD()
{
    $conn = new mysqli('localhost', 'bistro_fdi', 'bistro_fdi', 'bistro_fdi');
	if ( $conn->connect_errno ) {
		echo "Error de conexión a la BD ({$conn->connect_errno}):  {$conn->connect_error}";
		exit();
	}
	if ( ! $conn->set_charset("utf8mb4")) {
		echo "Error al configurar la BD ({$conn->errno}):  {$conn->error}";
		exit();
	}
    return $conn;
}

define('USER_ROLE', 1);
define('WAITRESS_ROLE', 2);
define('COOK_ROLE', 3);
define('ADMIN_ROLE', 4);
