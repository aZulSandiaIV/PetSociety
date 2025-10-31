<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";
require_once "config_view_post.php";
?>

<?php

// --- LÓGICA PARA EL MAPA DE AVISTAMIENTOS ---
$sql_avistamientos = "SELECT latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
                      FROM avistamientos 
                      ORDER BY fecha_avistamiento DESC 
                      LIMIT 50";

$avistamientos_json = "[]";
if ($result_avistamientos = $conexion->query($sql_avistamientos)) {
    $avistamientos_mapa = [];
    while ($row = $result_avistamientos->fetch_assoc()) {
        // Preparamos los datos para el popup del mapa
        $row['popup_html'] = "
            <div>
                <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Avistamiento' style='width:150px; height:auto; border-radius:4px;'>
                <p>" . htmlspecialchars($row['descripcion']) . "</p>
                <small>Visto el: " . date('d/m/Y H:i', strtotime($row['fecha_avistamiento'])) . "</small>
            </div>
        ";
        $avistamientos_mapa[] = $row;
    }
    $avistamientos_json = json_encode($avistamientos_mapa);
}

// --- LÓGICA PARA EL MAPA DE REPORTES DE PERDIDOS ---
$sql_perdidos = "SELECT 
                    r.latitud, r.longitud,
                    a.nombre, a.imagen_url,
                    p.titulo, p.id_publicacion
                 FROM reportes_perdidos r
                 JOIN animales a ON r.id_animal = a.id_animal
                 JOIN publicaciones p ON a.id_animal = p.id_animal
                 WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL AND a.estado = 'Perdido'
                 ORDER BY p.fecha_publicacion DESC
                 LIMIT 50";

$perdidos_json = "[]";
if ($result_perdidos = $conexion->query($sql_perdidos)) {
    $perdidos_mapa = [];
    while ($row = $result_perdidos->fetch_assoc()) {
        $row['popup_html'] = "
            <div style='text-align:center;'>
                <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Foto de " . htmlspecialchars($row['nombre']) . "' style='width:150px; height:auto; border-radius:4px;'>
                <p><strong>¡SE BUSCA!</strong><br>" . htmlspecialchars($row['titulo']) . "</p>
            </div>";
        $perdidos_mapa[] = $row;
    }
    $perdidos_json = json_encode($perdidos_mapa);
}
$conexion->close();

// 3. Incluir la vista
// El archivo de la vista ahora tendrá acceso a la variable $animales
require 'index.view.php';

?>