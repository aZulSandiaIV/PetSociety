<?php
    include "../config.php";

    $allowed_filters = [
        'status' => ['Adopción', 'Hogar Temporal', 'Perdido'],
        'race'   => ['Perro', 'Gato', ], // 'Otro' se maneja como valor excluyente
        'size'   => ['Pequeño', 'Mediano', 'Grande']
    ];

    /*
        //Parametros via get para solicitar/publicaciones.php
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

    $where_clauses = [];

    // Realizo esta solucion pensando status como el filtro principal.
    $where_clauses[] = "a.estado NOT IN ('Adoptado', 'Encontrado')";
    
    if (isset($_GET['status']) && in_array($_GET['status'], $allowed_filters['status'], true)) {
        $where_clauses[] = "p.tipo_publicacion = '" . $conexion->real_escape_string($_GET['status']) . "'";
    }elseif (isset($_GET['filter_status_null']) && $_GET['filter_status_null'] === '1') {
        $where_clauses = [];
    }
    

    if (isset($_GET['refugio']) && $_GET['refugio'] === '1') {
        $where_clauses[] = "u.es_refugio = 1";
    }

    if (isset($_GET['race']) && in_array($_GET['race'], $allowed_filters['race'], true)) {
        $filtro_raza = $conexion->real_escape_string($_GET['race']);
        $where_clauses[] = "a.especie = '{$filtro_raza}'";
    }elseif (isset($_GET['race']) && $_GET['race'] === 'Otro') {
        $where_clauses[] = "a.especie NOT IN ('Perro', 'Gato')";
    }
    if (isset($_GET['size']) && in_array($_GET['size'], $allowed_filters['size'], true)) {
        $filtro_tamano = $conexion->real_escape_string($_GET['size']);
        $where_clauses[] = "a.tamaño = '{$filtro_tamano}'";
    }
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $filtro_usuario = (int)$_GET['user_id'];
        $where_clauses[] = "p.id_usuario_publicador = {$filtro_usuario} ";
    }
    if (isset($_GET['animal_id']) && is_numeric($_GET['animal_id'])) {
        $filtro_animal = (int)$_GET['animal_id'];
        $where_clauses[] = "p.id_animal = {$filtro_animal} ";
    }

    if (isset($_GET['date']) && is_numeric($_GET['date'])) {
        $date = (int)$_GET['date'];
        $where_clauses[] = "p.fecha_publicacion >= DATE_SUB(NOW(), INTERVAL {$date} DAY)";
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = $conexion->real_escape_string($_GET['search']);
        $where_clauses[] = "(p.titulo LIKE '%{$search_term}%' OR p.contenido LIKE '%{$search_term}%' OR a.nombre LIKE '%{$search_term}%' OR a.raza LIKE '%{$search_term}%' OR p.ubicacion_texto LIKE '%{$search_term}%')";
    }

    if (isset($_GET['ubication']) && $_GET['ubication'] === '1') {
        $where_clauses[] = "p.latitud IS NOT NULL AND p.longitud IS NOT NULL ";
    }


    $sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado, a.tamaño, a.edad, a.color, a.genero,
               p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido, p.latitud, p.longitud,
               u.es_refugio
            FROM publicaciones p 
            JOIN animales a ON p.id_animal = a.id_animal 
            JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario";
    
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $sql .= "ORDER BY p.fecha_publicacion DESC";

    if(isset($_GET['cargar_apartir'])){ //Datos seguros
        $Cargar_Apartir = (int)$_GET['cargar_apartir'];
    }
    if(isset($_GET['cargar_cantidad'])){
        $Cargar_Cantidad = (int)$_GET['cargar_cantidad'];
    }

    $sql = isset($Cargar_Cantidad) ? $sql . " LIMIT " . $Cargar_Cantidad : $sql;
    $sql = isset($Cargar_Apartir) ? $sql . " OFFSET " . $Cargar_Apartir : $sql;

    $animales = [];

    $result = $conexion->query($sql);
    if ($result === false) {
        http_response_code(500); // Internal Server Error
        error_log("SQL error en solicitar/publicaciones.php: " . $conexion->error);
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
                'genero' => htmlspecialchars($row['genero'] ?? ''),
                'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100))),
                'descripcion' => nl2br(htmlspecialchars($row['contenido'])), // Descripción completa para el modal
                'latitud' => $row['latitud'],
                'longitud' => $row['longitud']
            ];
        /*
        if (!empty($row['latitud']) && !empty($row['longitud'])) {
            $animales[count($animales)-1]['latitud'] = $row['latitud'];
            $animales[count($animales)-1]['longitud'] = $row['longitud'];
        }*/
    }

    $result->free();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($animales);
?>