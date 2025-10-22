<?php
define('DB_SERVER', '127.0.0.1'); 
define('DB_USERNAME', 'root');      
define('DB_PASSWORD', '');         
define('DB_NAME', 'petsociety');

$conexion = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conexion === false){
    die("ERROR: No se pudo conectar. " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres a utf8mb4 para soportar caracteres especiales y emojis
$conexion->set_charset("utf8mb4");

?>