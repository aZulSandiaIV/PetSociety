<?php
    include "../config.php";

    $sql = "SELECT id_usuario, nombre, email, telefono, foto_perfil_url, 
                   (SELECT COUNT(p.id_publicacion) FROM publicaciones p JOIN animales a ON p.id_animal = a.id_animal WHERE p.id_usuario_publicador = u.id_usuario AND a.estado NOT IN ('Adoptado', 'Encontrado')) AS num_publicaciones 
            FROM usuarios u 
            WHERE es_refugio = 1";

    $refugios = [];

    if ($result = $conexion->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $refugios[] = $row;
        }
        $result->free();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($refugios);

?>