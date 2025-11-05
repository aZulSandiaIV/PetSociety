<?php
    include "config_view_post.php";
    include "config.php";

    /*
        //Parametros via get para solicitar_publicaciones.php
        filter_status_null: si es 1, aplica filtro por estado de publicacion
        refugio: si es 1, filtra solo publicaciones de refugios
        status: filtro por estado de publicacion (En Adopción, Hogar Temporal, Perdido)
        race: filtro por raza (Perro, Gato, Otro)
        size: filtro por tamaño (Pequeño, Mediano, Grande)
        date: filtro por fecha de publicacion (en dias)
        search: filtro por texto en titulo, contenido o ubicacion
        user_id: filtro por id de usuario publicador
        cargar_apartir: desde que registro cargar
        cargar_cantidad: cantidad de registros a cargar
    */

    $Cargar_Apartir = isset($_GET['cargar_apartir']) ? (int)$_GET['cargar_apartir'] : 0;
    $Cargar_Cantidad = isset($_GET['cargar_cantidad']) ? (int)$_GET['cargar_cantidad'] : 1;

    $where_Clause = '';

    
    if (isset($_GET['status']) && in_array($_GET['status'], $allowed_status_filters, true)) {
        $filtro_estado = $conexion->real_escape_string($_GET['status']);
        $where_Clause .= " WHERE p.tipo_publicacion = '{$filtro_estado}'";

    }elseif (isset($_GET['filter_status_null']) && $_GET['filter_status_null'] === '1') {
        $where_Clause = "";
        
    }else{
        $where_Clause .= " WHERE p.tipo_publicacion IN ('Adopción', 'Hogar Temporal', 'Perdido')";
    }
    

    if (isset($_GET['refugio']) && $_GET['refugio'] === '1') {
        $where_Clause .= empty($where_Clause) ? " WHERE u.es_refugio = 1" : " AND u.es_refugio = 1";
    }

    if (isset($_GET['race']) && in_array($_GET['race'], $allowed_races_filters, true)) {
        $filtro_raza = $conexion->real_escape_string($_GET['race']);
        $where_Clause .= empty($where_Clause) ? " WHERE a.especie = '{$filtro_raza}'" : " AND a.especie = '{$filtro_raza}'";
    }
    if (isset($_GET['size']) && in_array($_GET['size'], $allowed_sizes_filters, true)) {
        $filtro_tamano = $conexion->real_escape_string($_GET['size']);
        $where_Clause .= empty($where_Clause) ? " WHERE a.tamano = '{$filtro_tamano}'" : " AND a.tamano = '{$filtro_tamano}'";
    }
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $filtro_usuario = (int)$_GET['user_id'];
        $where_Clause .= empty($where_Clause) ? " WHERE p.id_usuario_publicador = {$filtro_usuario}" : " AND p.id_usuario_publicador = {$filtro_usuario}";
    }

    if (isset($_GET['date']) && is_numeric($_GET['date'])) {
        $date = (int)$_GET['date'];
        $where_Clause[] = empty($where_Clause) ?
        " WHERE p.fecha_publicacion >= DATE_SUB(NOW(), INTERVAL {$date} DAY)"
        :
        " AND p.fecha_publicacion >= DATE_SUB(NOW(), INTERVAL {$date} DAY)";
    }

    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $search_Term = '%' . strtolower($conexion->real_escape_string(trim($_GET['search']))) . '%';
        $where_Clause = empty($where_Clause) ? 
        " WHERE (LOWER(p.titulo) LIKE '{$search_Term}' OR LOWER(p.contenido) LIKE '{$search_Term}' OR LOWER(p.ubicacion_texto) LIKE '{$search_Term}')"
        :
        " AND (LOWER(p.titulo) LIKE '{$search_Term}' OR LOWER(p.contenido) LIKE '{$search_Term}' OR LOWER(p.ubicacion_texto) LIKE '{$search_Term}')";
    }


    $sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado, a.tamaño, a.edad, a.color,
               p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido, p.latitud, p.longitud,
               u.es_refugio
            FROM publicaciones p 
            JOIN animales a ON p.id_animal = a.id_animal 
            JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario
            ". $where_Clause ."
            ORDER BY p.fecha_publicacion DESC
            LIMIT " . $Cargar_Cantidad . " OFFSET " . $Cargar_Apartir;

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

    $result->free();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($animales);
?>