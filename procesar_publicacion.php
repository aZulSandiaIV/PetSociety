<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // --- 0. Manejar la subida de la imagen ---
        $imagen_url = null;
        if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] == 0) {
            $directorio_subidas = 'uploads/';
            if (!is_dir($directorio_subidas)) {
                mkdir($directorio_subidas, 0777, true);
            }

            $nombre_archivo = uniqid() . '-' . basename($_FILES["foto_animal"]["name"]);
            $ruta_archivo = $directorio_subidas . $nombre_archivo;
            $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

            // Validar tipo de archivo
            $extensiones_validas = array("jpg", "jpeg", "png", "gif");
            if (!in_array($tipo_archivo, $extensiones_validas)) {
                throw new Exception("Error: Solo se permiten archivos JPG, JPEG, PNG y GIF.");
            }

            // Validar tamaño del archivo (ej: 5MB máximo)
            if ($_FILES["foto_animal"]["size"] > 5 * 1024 * 1024) {
                throw new Exception("Error: El archivo es demasiado grande. Máximo 5MB.");
            }

            if (move_uploaded_file($_FILES["foto_animal"]["tmp_name"], $ruta_archivo)) {
                $imagen_url = $ruta_archivo;
            } else {
                throw new Exception("Error al subir el archivo de imagen.");
            }
        }

        // --- 1. Insertar el animal en la tabla `animales` ---
        $sql_animal = "INSERT INTO animales (nombre, especie, raza, imagen_url, estado, genero, descripcion) VALUES (?, ?, ?, ?, ?, ?, 'Descripción pendiente')";
        
        if ($stmt_animal = $conexion->prepare($sql_animal)) {
            // Determinar el estado inicial del animal basado en el tipo de publicación
            $estado_animal = 'En Adopción'; // Por defecto
            if ($_POST['tipo_publicacion'] == 'Hogar Temporal') {
                $estado_animal = 'Hogar Temporal';
            } elseif ($_POST['tipo_publicacion'] == 'Perdido') {
                $estado_animal = 'Perdido';
            }

            $stmt_animal->bind_param("ssssss", 
                $_POST['nombre_animal'], 
                $_POST['especie'], 
                $_POST['raza'],
                $imagen_url,
                $estado_animal,
                $_POST['genero']
            );

            $stmt_animal->execute();
            
            // Obtener el ID del animal recién insertado
            $id_animal_nuevo = $stmt_animal->insert_id;
            $stmt_animal->close();

            // --- 2. Insertar la publicación en la tabla `publicaciones` ---
            $sql_pub = "INSERT INTO publicaciones (id_animal, id_usuario_publicador, titulo, contenido, tipo_publicacion, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?, ?)";

            if ($stmt_pub = $conexion->prepare($sql_pub)) {
                // Usamos 'd' para los decimales (latitud y longitud)
                $lat = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
                $lon = !empty($_POST['longitud']) ? $_POST['longitud'] : null;

                $stmt_pub->bind_param("iisssdd",
                    $id_animal_nuevo,
                    $_SESSION['id_usuario'],
                    $_POST['titulo'],
                    $_POST['contenido'],
                    $_POST['tipo_publicacion'],
                    $lat,
                    $lon
                );

                $stmt_pub->execute();
                $stmt_pub->close();
            } else {
                throw new Exception("Error al preparar la consulta de publicación.");
            }

            // --- 3. (Opcional) Insertar en la tabla `reportes_perdidos` ---
            if ($_POST['tipo_publicacion'] == 'Perdido') {
                $sql_perdido = "INSERT INTO reportes_perdidos (id_animal, id_usuario_reportador, ultima_ubicacion_vista, caracteristicas_distintivas, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_perdido = $conexion->prepare($sql_perdido)) {
                    // Usamos 'd' para los decimales (latitud y longitud)
                    $lat = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
                    $lon = !empty($_POST['longitud']) ? $_POST['longitud'] : null;

                    $stmt_perdido->bind_param("iissdd", // El tipo de dato para lat y lon es 'd' (double)
                        $id_animal_nuevo,
                        $_SESSION['id_usuario'],
                        $_POST['ubicacion_texto'], // Usamos el nuevo nombre del campo
                        $_POST['caracteristicas_distintivas'],
                        $lat,
                        $lon
                    );
                    $stmt_perdido->execute();
                    $stmt_perdido->close();
                }
            }

            // Si todo fue bien, confirmar la transacción
            $conexion->commit();
            echo "¡Publicación creada con éxito! Redirigiendo a la página principal...";
            header("refresh:3;url=index.php");

        } else {
            throw new Exception("Error al preparar la consulta de animal.");
        }
    } catch (Exception $e) {
        // Si algo falla, revertir la transacción
        $conexion->rollback();
        die("Error al crear la publicación: " . $e->getMessage());
    }

    $conexion->close();
}
?>