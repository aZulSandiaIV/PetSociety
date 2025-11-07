<?php
session_start();
require_once "config.php";
ob_start(); // Iniciar el búfer de salida
?>

<?php

// Usamos la función para obtener las publicaciones.
// Pasamos los filtros desde $_GET a la función.
$animales = obtener_publicaciones($conexion, $_GET);

// --- LÓGICA PARA EL MAPA ---
$map_data = mapa_avistamientos($conexion, $animales);
$avistamientos_json = $map_data['avistamientos_json'];
$perdidos_json = $map_data['perdidos_json'];

// --- LÓGICA PARA EL MAPA DE PUBLICACIONES (ADOPCIÓN, HOGAR TEMPORAL, REFUGIOS) ---
$publicaciones_json = mapa_publicaciones($animales);


$conexion->close();

// 3. Incluir la vista
// El archivo de la vista ahora tendrá acceso a la variable $animales
require 'index.view.php';

ob_end_flush(); // Vaciar el búfer de salida
?>