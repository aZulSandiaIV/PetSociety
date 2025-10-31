<?php
    include "config_view_post.php";
    include "config.php";
    
    $where_clause = "";
    if (in_array($_GET['Filtro'], $allowed_status_filters)) {
        $where_clause = "WHERE a.estado = '" . $_GET['Filtro'] . "'";

    } elseif ($_GET['Filtro'] == 'Refugio') {
        $where_clause = "WHERE u.es_refugio = 1";

    } else {
        $where_clause = "WHERE a.estado IN ('En Adopción', 'Hogar Temporal', 'Perdido')";
    }

    $sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado,
                p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido,
                u.es_refugio
            FROM publicaciones p 
            JOIN animales a ON p.id_animal = a.id_animal 
            JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario
            $where_clause
            ORDER BY p.fecha_publicacion
            LIMIT 5 OFFSET " . $_GET['CargarApartirDe'] . "";

    $animales = [];
    if ($result = $conexion->query($sql)) {

        if ($result->num_rows > 0) {

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
                    'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100)))
                ];
            }
        }else{
            http_response_code(204); // No Content
            exit;
        }
        $result->free();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($animales);

?>