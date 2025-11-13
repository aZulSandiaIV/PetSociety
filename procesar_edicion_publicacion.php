<?php
session_start();

// 1. Verificar que el usuario esté logueado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// 2. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: index.php");
    exit;
}

// 3. Recoger y validar los datos del formulario
$id_publicacion = filter_input(INPUT_POST, 'id_publicacion', FILTER_VALIDATE_INT);
$id_animal = filter_input(INPUT_POST, 'id_animal', FILTER_VALIDATE_INT);
$id_usuario_actual = $_SESSION['id_usuario'];

if (!$id_publicacion || !$id_animal) {
    $_SESSION['mensaje'] = "Error: Faltan identificadores clave.";
    $_SESSION['tipo_mensaje'] = "error";
    header("location: mi_perfil.php");
    exit;
}

// 4. Verificar que el usuario actual es el dueño de la publicación
$sql_permiso = "SELECT id_usuario_publicador FROM publicaciones WHERE id_publicacion = ?";
if ($stmt_permiso = $conexion->prepare($sql_permiso)) {
    $stmt_permiso->bind_param("i", $id_publicacion);
    $stmt_permiso->execute();
    $result_permiso = $stmt_permiso->get_result();
    if ($fila = $result_permiso->fetch_assoc()) {
        if ($fila['id_usuario_publicador'] != $id_usuario_actual) {
            $_SESSION['mensaje'] = "Acceso denegado. No tienes permiso para editar esta publicación.";
            $_SESSION['tipo_mensaje'] = "error";
            header("location: mi_perfil.php");
            exit;
        }
    } else {
        $_SESSION['mensaje'] = "La publicación que intentas editar no existe.";
        $_SESSION['tipo_mensaje'] = "error";
        header("location: mi_perfil.php");
        exit;
    }
    $stmt_permiso->close();
}

// 5. Recoger el resto de los datos
$titulo = trim($_POST['titulo']);
$contenido = trim($_POST['contenido']);
$nombre_animal = trim($_POST['nombre_animal']);
$especie = trim($_POST['especie']);
$ubicacion_texto = $_POST['ubicacion_texto'] ?? null;
$latitud = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
$longitud = !empty($_POST['longitud']) ? $_POST['longitud'] : null;

$raza = trim($_POST['raza']);
$edad = trim($_POST['edad']);
$tamaño = trim($_POST['tamaño']);
$color = trim($_POST['color']);
$genero = trim($_POST['genero']);

$conexion->begin_transaction();

try {
    // 6. Actualizar la tabla 'publicaciones'
    $sql_pub = "UPDATE publicaciones SET titulo = ?, contenido = ?, ubicacion_texto = ?, latitud = ?, longitud = ? WHERE id_publicacion = ?";
    $stmt_pub = $conexion->prepare($sql_pub);
    $stmt_pub->bind_param("sssddi", $titulo, $contenido, $ubicacion_texto, $latitud, $longitud, $id_publicacion);
    $stmt_pub->execute();
    $stmt_pub->close();

    // 7. Actualizar la tabla 'animales'
    $sql_animal = "UPDATE animales SET nombre = ?, especie = ?, raza = ?, edad = ?, tamaño = ?, color = ?, genero = ? WHERE id_animal = ?";
    $stmt_animal = $conexion->prepare($sql_animal);
    $stmt_animal->bind_param("sssssssi", $nombre_animal, $especie, $raza, $edad, $tamaño, $color, $genero, $id_animal);
    $stmt_animal->execute();
    $stmt_animal->close();

    // 8. Manejar la subida de la nueva foto (si existe)
    if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] == 0) {
        // Obtener la ruta de la imagen actual para borrarla
        $sql_foto_actual = "SELECT imagen_url FROM animales WHERE id_animal = ?";
        $stmt_foto_actual = $conexion->prepare($sql_foto_actual);
        $stmt_foto_actual->bind_param("i", $id_animal);
        $stmt_foto_actual->execute();
        $result_foto = $stmt_foto_actual->get_result();
        if ($fila_foto = $result_foto->fetch_assoc()) {
            if (!empty($fila_foto['imagen_url']) && file_exists($fila_foto['imagen_url'])) {
                unlink($fila_foto['imagen_url']); // Borra el archivo antiguo
            }
        }
        $stmt_foto_actual->close();

        // Procesar la nueva imagen
        $directorio_subidas = 'uploads/';
        if (!is_dir($directorio_subidas)) {
            mkdir($directorio_subidas, 0777, true);
        }
        $nombre_archivo = uniqid() . '-' . basename($_FILES["foto_animal"]["name"]);
        $ruta_archivo = $directorio_subidas . $nombre_archivo;
        
        // Validar y mover el archivo
        $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));
        $extensiones_validas = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($tipo_archivo, $extensiones_validas) || $_FILES["foto_animal"]["size"] > 5 * 1024 * 1024) {
            throw new Exception("Archivo de imagen no válido o demasiado grande.");
        }

        if (move_uploaded_file($_FILES["foto_animal"]["tmp_name"], $ruta_archivo)) {
            // Actualizar la URL de la imagen en la base de datos
            $sql_update_foto = "UPDATE animales SET imagen_url = ? WHERE id_animal = ?";
            $stmt_update_foto = $conexion->prepare($sql_update_foto);
            $stmt_update_foto->bind_param("si", $ruta_archivo, $id_animal);
            $stmt_update_foto->execute();
            $stmt_update_foto->close();
        } else {
            throw new Exception("Error al mover el archivo subido.");
        }
    }

    // 9. Si todo fue bien, confirmar los cambios
    $conexion->commit();
    $_SESSION['mensaje'] = "Publicación actualizada correctamente.";
    $_SESSION['tipo_mensaje'] = "success";

} catch (Exception $e) {
    // 10. Si algo falló, revertir todos los cambios
    $conexion->rollback();
    $_SESSION['mensaje'] = "Error al actualizar la publicación: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
}

$conexion->close();

// 11. Redirigir al perfil del usuario
header("location: mi_perfil.php");
exit;
?>