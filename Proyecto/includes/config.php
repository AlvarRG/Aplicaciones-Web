<?php

/**
 * Parámetros de conexión a la BD
 */
define('BD_HOST', 'localhost');
//define('BD_HOST', '172.17.0.2');
define('BD_NAME', 'bistro_fdi');
define('BD_USER', 'bistro_fdi');
define('BD_PASS', 'bistro_fdi');

/**
 * Parámetros de configuración utilizados para generar las URLs y las rutas a ficheros en la aplicación
 */
define('RAIZ_APP', __DIR__);
define('RUTA_APP', '/AW/2026/EJ2/EJ2_2026/06-inicio/06-inicio');
define('RUTA_IMGS', RUTA_APP.'/img');
define('RUTA_CSS', RUTA_APP.'/CSS');
define('RUTA_JS', RUTA_APP.'/js');

/**
 * Autoload de clases siguiendo PSR-4 para el namespace es\ucm\fdi\aw
 * 
 * @see https://www.php-fig.org/psr/psr-4/examples/#closure-example
 */
spl_autoload_register(function ($class) {
    $prefix = 'es\\ucm\\fdi\\aw\\';
    $base_dir = __DIR__ . '/clases/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Configuración del soporte de UTF-8, localización (idioma y país) y zona horaria
 */
ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF-8');
date_default_timezone_set('Europe/Madrid');

//Inicializa la aplicación
use es\ucm\fdi\aw\Aplicacion;

$app = Aplicacion::getInstance();
$app->init(['host'=>BD_HOST, 'bd'=>BD_NAME, 'user'=>BD_USER, 'pass'=>BD_PASS]);

/**
 * @see http://php.net/manual/en/function.register-shutdown-function.php
 * @see http://php.net/manual/en/language.types.callable.php
 */
register_shutdown_function([$app, 'shutdown']);