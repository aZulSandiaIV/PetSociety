<?php
session_start();
require_once "config.php";
 
// Usamos la función refactorizada para obtener las publicaciones.
// Pasamos directamente el array $_GET que contiene todos los filtros.
$animales = obtener_publicaciones($conexion, $_GET);
 
$conexion->close();
 
// Si no se encontraron animales, enviamos una respuesta "No Content".
if (empty($animales)) {
    http_response_code(204); // No Content
    exit;
}
 
// Enviamos la respuesta en formato JSON.
header('Content-Type: application/json; charset=utf-8');
echo json_encode($animales);
?>