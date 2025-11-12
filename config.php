<?php
/*
 * Fichero de configuración para la conexión a la base de datos.
 */

// Parámetros de la base de datos
define('DB_SERVER', '127.0.0.1'); 
define('DB_USERNAME', 'root');      
define('DB_PASSWORD', '');         
define('DB_NAME', 'petsociety');

// URL base de la aplicación
define('ROOT_URL', 'http://localhost/Petsociety/');



/* Intenta conectar a la base de datos MySQL */
$conexion = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Comprobar la conexión
if($conexion === false){
    die("ERROR: No se pudo conectar. " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres a utf8mb4 para soportar caracteres especiales y emojis
$conexion->set_charset("utf8mb4");

// Incluir el archivo de funciones para que estén disponibles globalmente
require_once "funciones.php";

?>
 