<?php
    include "config_view_post.php";
    include "config.php";

    /*
        //Parametros via get para solicitar_publicaciones.php
        cargar_apartir: desde que registro cargar
        cargar_cantidad: cantidad de registros a cargar
        status: filtro por estado de publicacion (En Adopción, Hogar Temporal, Perdido)
        race: filtro por raza (Perro, Gato, Otro)
        size: filtro por tamaño (Pequeño, Mediano, Grande)
        user_id: filtro por id de usuario publicador
    */

    $Cargar_Apartir = isset($_GET['cargar_apartir']) ? (int)$_GET['cargar_apartir'] : 0;
    $Cargar_Cantidad = isset($_GET['cargar_cantidad']) ? (int)$_GET['cargar_cantidad'] : 1;

    $where_Clause = '';

    if (isset($_GET['status']) && in_array($_GET['status'], $allowed_status_filters, true)) {
        $filtro_estado = $conexion->real_escape_string($_GET['status']);
        $where_Clause = " WHERE p.tipo_publicacion = '{$filtro_estado}'";

    }elseif (isset($_GET['refugio']) && $_GET['refugio'] === '1') { // Refugio aparte, solo publicaciones de refugios
        $where_Clause .= " WHERE u.es_refugio = 1";
    }else{
        $where_Clause = " WHERE p.tipo_publicacion IN ('Adopción', 'Hogar Temporal', 'Perdido')";
    }

    //Decidi dejar la clausula where por si en un futuro decidiamos agregar status nulo, osea mostrar todo sin importar si son encontrados o tal
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

    
    $sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, p.tipo_publicacion,
                p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido,
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
            'estado' => htmlspecialchars($row['tipo_publicacion']),
            'especie' => htmlspecialchars($row['especie']),
            'raza' => htmlspecialchars($row['raza']),
            'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100)))
        ];
    }

    $result->free();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($animales);
?>