<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar datos básicos
    if (empty($_POST['latitud']) || empty($_POST['longitud']) || !isset($_FILES['foto_avistamiento']) || $_FILES['foto_avistamiento']['error'] != 0) {
        die("Error: Faltan datos de ubicación o la foto. Asegúrate de obtener la ubicación antes de enviar.");
    }

    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];
    $descripcion = trim($_POST['descripcion']);
    $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

    // --- Manejar la subida de la imagen ---
    $imagen_url = null;
    try {
        $directorio_subidas = 'uploads/avistamientos/';
        if (!is_dir($directorio_subidas)) {
            mkdir($directorio_subidas, 0777, true);
        }

        $nombre_archivo = uniqid() . '-' . basename($_FILES["foto_avistamiento"]["name"]);
        $ruta_archivo = $directorio_subidas . $nombre_archivo;
        $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

        $extensiones_validas = array("jpg", "jpeg", "png", "gif");
        if (!in_array($tipo_archivo, $extensiones_validas)) {
            throw new Exception("Error: Solo se permiten archivos de imagen (JPG, PNG, GIF).");
        }

        if ($_FILES["foto_avistamiento"]["size"] > 10 * 1024 * 1024) { // 10MB Límite
            throw new Exception("Error: El archivo es demasiado grande (máximo 10MB).");
        }

        if (move_uploaded_file($_FILES["foto_avistamiento"]["tmp_name"], $ruta_archivo)) {
            $imagen_url = $ruta_archivo;
        } else {
            throw new Exception("Error al subir la imagen.");
        }

        // --- Insertar en la base de datos ---
        $sql = "INSERT INTO avistamientos (latitud, longitud, imagen_url, descripcion, id_usuario_reporta) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ddssi", $latitud, $longitud, $imagen_url, $descripcion, $id_usuario);
            if ($stmt->execute()) {
                header("Location: index.php");
                exit;
            } else {
                throw new Exception("Error al guardar el reporte en la base de datos.");
            }
            $stmt->close();
        }

    } catch (Exception $e) {
        // En caso de error, podemos borrar la imagen si ya se subió para no dejar basura.
        if ($imagen_url && file_exists($imagen_url)) { unlink($imagen_url); }
        die("Error al procesar el reporte: " . $e->getMessage());
    }

    $conexion->close();
}
