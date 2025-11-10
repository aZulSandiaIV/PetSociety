<?php
    include "../config.php";

    $sql = "SELECT id_usuario, nombre, email, telefono, foto_perfil_url as foto, 
                   (SELECT COUNT(p.id_publicacion) FROM publicaciones p JOIN animales a ON p.id_animal = a.id_animal WHERE p.id_usuario_publicador = u.id_usuario AND a.estado NOT IN ('Adoptado', 'Encontrado')) AS num_publicaciones 
            FROM usuarios u 
            WHERE es_refugio = 1";

    if (isset($_GET['cargar_apartir']) && isset($_GET['cargar_cantidad'])) {
        $cargar_apartir = intval($_GET['cargar_apartir']);
        $cargar_cantidad = intval($_GET['cargar_cantidad']);
        $sql .= " LIMIT $cargar_apartir, $cargar_cantidad";
    }

    $refugios = [];

    if ($result = $conexion->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            
            if (!empty($row['foto']) && file_exists("../" . $row['foto'])) {
                $row['foto'] = [
                    'tipo' => 'foto',
                    'url' => $row['foto'],
                    'iniciales' => '',
                    'color' => ''
                ];
            }else{
                $row['foto'] = [
                    'tipo' => 'avatar',
                    'url' => '',
                    'iniciales' => generarIniciales($row['nombre']),
                    'color' => generarColorAvatar($row['nombre'] . $row['id_usuario'])
                ];    
            }
            
            $refugios[] = $row;
            if (!empty($row['foto_perfil_url']) && file_exists($$row['foto_perfil_url'])) {
                $refugios['foto_perfil'] = [
                    'tipo' => 'foto',
                    'url' => $row['foto_perfil_url'],
                    'iniciales' => '',
                    'color' => ''
                ];
            }else{
                $refugio['foto_perfil'] = [
                    'tipo' => 'avatar',
                    'url' => '',
                    'iniciales' => generarIniciales($row['nombre']),
                    'color' => generarColorAvatar($row['nombre'] . $row['id_usuario'])
                ];    
            }
            
            
        }
        $result->free();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($refugios);

?>