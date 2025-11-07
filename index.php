<?php
session_start();
require_once "config.php";
?>

<?php

    $where_Clause = '';

    
    if (isset($_GET['status']) && in_array($_GET['status'], $allowed_status_filters, true)) {
        $filtro_estado = $conexion->real_escape_string($_GET['status']);
        $where_Clause .= " WHERE p.tipo_publicacion = '{$filtro_estado}'";

    }elseif (isset($_GET['filter_status_null']) && $_GET['filter_status_null'] === '1') {
        $where_Clause = "";
        
    }else{
        $where_Clause .= " WHERE p.tipo_publicacion IN ('Adopción', 'Hogar Temporal', 'Perdido')";
    }

    $sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado, a.tamaño, a.edad, a.color,
               p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido, p.latitud, p.longitud,
               u.es_refugio
            FROM publicaciones p 
            JOIN animales a ON p.id_animal = a.id_animal 
            JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario
            ORDER BY p.fecha_publicacion DESC";

    $animales = [];

    $result = $conexion->query($sql);
    if ($result === false) {
        http_response_code(500); // Internal Server Error
        error_log("SQL error en solicitar_publicaciones.php: " . $conexion->error);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Database error']);
        exit;
    }

    if ($result->num_rows === 0) {
        http_response_code(204); // No Content
        $result->free();
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        $animales[] = [
            'id_animal' => $row['id_animal'],
            'id_publicacion' => $row['id_publicacion'],
            'id_publicador' => $row['id_usuario_publicador'],
            'es_refugio' => $row['es_refugio'],
            'imagen' => $row['imagen_url'] ? htmlspecialchars($row['imagen_url']) : 'https://via.placeholder.com/300x200.png?text=Sin+Foto',
            'nombre' => htmlspecialchars($row['nombre']),
            'titulo' => htmlspecialchars($row['titulo']),
            'estado' => htmlspecialchars($row['estado']),
            'especie' => htmlspecialchars($row['especie']),
            'raza' => htmlspecialchars($row['raza']),
            'tamaño' => htmlspecialchars($row['tamaño'] ?? ''),
            'edad' => htmlspecialchars($row['edad'] ?? ''),
            'color' => htmlspecialchars($row['color'] ?? ''),
            'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100)))
        ];

        if (!empty($row['latitud']) && !empty($row['longitud'])) {
            $animales[count($animales)-1]['latitud'] = $row['latitud'];
            $animales[count($animales)-1]['longitud'] = $row['longitud'];
        }
    }

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

?>