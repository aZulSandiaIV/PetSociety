<?php
session_start();
require_once "config.php";
require_once "config_view_post.php";
?>

<?php

// --- LÓGICA PARA EL MAPA DE AVISTAMIENTOS ---
$sql_avistamientos = "SELECT id_avistamiento, latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
                      FROM avistamientos 
                      WHERE estado = 'Visto'
                      ORDER BY fecha_avistamiento DESC
                      LIMIT 50";

$avistamientos_json = "[]";
if ($result_avistamientos = $conexion->query($sql_avistamientos)) {
    $avistamientos_mapa = [];
    while ($row = $result_avistamientos->fetch_assoc()) {
        // Preparamos los datos para el popup del mapa
        $botones_avistamiento = '';
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
            $botones_avistamiento = "
                <div style='margin-top:10px; display:flex; justify-content:space-around;'>
                    <a href='actualizar_avistamiento.php?id={$row['id_avistamiento']}&accion=sigue_aqui' style='font-size:0.8em; padding: 4px 8px; background-color:#97BC62; color:white; text-decoration:none; border-radius:4px;'>Sigue Aquí</a>
                    <a href='actualizar_avistamiento.php?id={$row['id_avistamiento']}&accion=no_esta' style='font-size:0.8em; padding: 4px 8px; background-color:#E57373; color:white; text-decoration:none; border-radius:4px;'>Ya no está</a>
                </div>";
        }
        $row['popup_html'] = "
            <div>
                <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Avistamiento' style='width:150px; height:auto; border-radius:4px;'>
                <p>" . htmlspecialchars($row['descripcion']) . "</p>
                <small>Visto el: " . date('d/m/Y H:i', strtotime($row['fecha_avistamiento'])) . "</small>
                {$botones_avistamiento}
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

// --- LÓGICA PARA EL MAPA DE PUBLICACIONES (ADOPCIÓN, HOGAR TEMPORAL, REFUGIOS) ---
$publicaciones_mapa = [];
foreach ($animales as $animal) {
    if (isset($animal['latitud']) && isset($animal['longitud'])) {
        // No incluimos los 'Perdido' porque ya se obtienen en su propia consulta
        if ($animal['estado'] != 'Perdido') {
            $popup_html = "
                <div style='text-align:center;'>
                    <img src='" . $animal['imagen'] . "' alt='Foto de " . $animal['nombre'] . "' style='width:150px; height:auto; border-radius:4px;'>
                    <p><strong>" . $animal['titulo'] . "</strong><br>(" . $animal['estado'] . ")</p>
                </div>";
            
            $publicaciones_mapa[] = [
                'latitud' => $animal['latitud'],
                'longitud' => $animal['longitud'],
                'popup_html' => $popup_html,
                'es_refugio' => $animal['es_refugio']
            ];
        }
    }
}
$publicaciones_json = json_encode($publicaciones_mapa);


$conexion->close();

// 3. Incluir la vista
// El archivo de la vista ahora tendrá acceso a la variable $animales
require 'index.view.php';

?>