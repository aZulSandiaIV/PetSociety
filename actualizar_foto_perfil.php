<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['id_usuario'];

    // === CASO 1: ELIMINAR FOTO DE PERFIL ===
    if (isset($_POST['eliminar_foto'])) {
        // Obtener la foto de perfil actual para eliminarla
        $sql_actual = "SELECT foto_perfil_url FROM usuarios WHERE id_usuario = ?";
        if ($stmt_actual = $conexion->prepare($sql_actual)) {
            $stmt_actual->bind_param("i", $id_usuario);
            $stmt_actual->execute();
            $result_actual = $stmt_actual->get_result();
            if ($row = $result_actual->fetch_assoc()) {
                $foto_actual = $row['foto_perfil_url'];
                // Eliminar archivo si existe
                if (!empty($foto_actual) && file_exists($foto_actual)) {
                    unlink($foto_actual);
                }
            }
            $stmt_actual->close();
        }
        
        // Actualizar la base de datos para quitar la foto
        $sql = "UPDATE usuarios SET foto_perfil_url = NULL WHERE id_usuario = ?";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("i", $id_usuario);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Foto de perfil eliminada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar la foto de perfil.";
                $_SESSION['tipo_mensaje'] = "error";
            }
            $stmt->close();
        }
        
        $conexion->close();
        header("location: mis_publicaciones.php");
        exit;
    }

    // === CASO 2: SUBIR/ACTUALIZAR FOTO DE PERFIL ===
    
    // Verificar si se recibió el archivo
    if (!isset($_FILES["foto_perfil"])) {
        $_SESSION['mensaje'] = "No se recibió ningún archivo. Error en el formulario.";
        $_SESSION['tipo_mensaje'] = "error";
        header("location: mis_publicaciones.php");
        exit;
    }

    // Verificar errores en la subida
    if ($_FILES["foto_perfil"]["error"] != 0) {
        $error_codes = [
            1 => "El archivo es demasiado grande (límite del servidor)",
            2 => "El archivo es demasiado grande (límite del formulario)",
            3 => "El archivo se subió parcialmente",
            4 => "No se seleccionó ningún archivo",
            6 => "Falta carpeta temporal",
            7 => "Error al escribir el archivo",
            8 => "Extensión PHP detuvo la subida"
        ];
        $error_msg = isset($error_codes[$_FILES["foto_perfil"]["error"]]) 
                     ? $error_codes[$_FILES["foto_perfil"]["error"]] 
                     : "Error desconocido: " . $_FILES["foto_perfil"]["error"];
        
        $_SESSION['mensaje'] = "Error al subir archivo: " . $error_msg;
        $_SESSION['tipo_mensaje'] = "error";
        header("location: mis_publicaciones.php");
        exit;
    }

    // Procesar la subida del archivo
    $archivo = $_FILES["foto_perfil"];
    
    // Validar tipo de archivo
    $tipos_permitidos = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
    $tipo_archivo = $archivo["type"];
    
    if (!in_array($tipo_archivo, $tipos_permitidos)) {
        $_SESSION['mensaje'] = "Solo se permiten archivos de imagen (JPG, JPEG, PNG, GIF).";
        $_SESSION['tipo_mensaje'] = "error";
        header("location: mis_publicaciones.php");
        exit;
    }
    
    // Validar tamaño del archivo (máximo 5MB)
    if ($archivo["size"] > 5 * 1024 * 1024) {
        $_SESSION['mensaje'] = "El archivo es demasiado grande. Máximo 5MB.";
        $_SESSION['tipo_mensaje'] = "error";
        header("location: mis_publicaciones.php");
        exit;
    }
    
    // Crear directorio si no existe
    $directorio_destino = "uploads/perfiles/";
    if (!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($archivo["name"], PATHINFO_EXTENSION);
    $nombre_archivo = "perfil_" . $id_usuario . "_" . uniqid() . "." . $extension;
    $ruta_destino = $directorio_destino . $nombre_archivo;
    
    // Obtener la foto de perfil actual para eliminarla
    $sql_actual = "SELECT foto_perfil_url FROM usuarios WHERE id_usuario = ?";
    if ($stmt_actual = $conexion->prepare($sql_actual)) {
        $stmt_actual->bind_param("i", $id_usuario);
        $stmt_actual->execute();
        $result_actual = $stmt_actual->get_result();
        if ($row = $result_actual->fetch_assoc()) {
            $foto_actual = $row['foto_perfil_url'];
            // Eliminar archivo anterior si existe
            if (!empty($foto_actual) && file_exists($foto_actual)) {
                unlink($foto_actual);
            }
        }
        $stmt_actual->close();
    }
    
    // Mover el archivo subido
    if (move_uploaded_file($archivo["tmp_name"], $ruta_destino)) {
        // Actualizar la base de datos
        $sql = "UPDATE usuarios SET foto_perfil_url = ? WHERE id_usuario = ?";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("si", $ruta_destino, $id_usuario);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Foto de perfil actualizada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar la base de datos.";
                $_SESSION['tipo_mensaje'] = "error";
                // Eliminar el archivo si no se pudo actualizar la BD
                unlink($ruta_destino);
            }
            $stmt->close();
        }
    } else {
        $_SESSION['mensaje'] = "Error al subir el archivo.";
        $_SESSION['tipo_mensaje'] = "error";
    }
    
    $conexion->close();
    header("location: mis_publicaciones.php");
    exit;
    
} else {
    header("location: mis_publicaciones.php");
    exit;
}
?>