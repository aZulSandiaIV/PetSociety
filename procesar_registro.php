<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar y limpiar datos
    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $es_refugio = isset($_POST['es_refugio']) ? 1 : 0;

    // --- Validaciones ---
    $errors = [];
    if (empty($nombre) || empty($dni) || empty($email) || empty($password)) {
        $errors[] = "Por favor, completa todos los campos obligatorios.";
    }

    if (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    if ($password != $confirm_password) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    // Verificar si el email ya existe
    $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Este email ya está registrado.";
        }
        $stmt->close();
    }

    // Verificar si el DNI ya existe
    $sql = "SELECT id_usuario FROM usuarios WHERE dni = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Este DNI ya está registrado.";
        }
        $stmt->close();
    }

    // Si no hay errores, proceder con la inserción
    if (empty($errors)) {
        // --- Manejo de la foto de perfil ---
        $foto_perfil_url = null; // Por defecto, no hay foto

        // 1. Verificar si se subió un archivo y no hubo errores
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
            $directorio_subidas = 'uploads/perfiles/';
            
            // 2. Crear el directorio si no existe
            if (!is_dir($directorio_subidas)) {
                mkdir($directorio_subidas, 0777, true);
            }

            // 3. Validar el archivo (tipo y tamaño)
            $nombre_archivo = uniqid('perfil_') . '-' . basename($_FILES["foto_perfil"]["name"]);
            $ruta_archivo = $directorio_subidas . $nombre_archivo;
            $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));
            $extensiones_validas = ["jpg", "jpeg", "png", "gif"];

            if (!in_array($tipo_archivo, $extensiones_validas)) {
                $errors[] = "El formato de la foto de perfil no es válido. Solo se permiten JPG, PNG o GIF.";
            } elseif ($_FILES["foto_perfil"]["size"] > 5 * 1024 * 1024) { // Límite de 5MB
                $errors[] = "La foto de perfil es demasiado grande (máximo 5MB).";
            }

            // 4. Mover el archivo si es válido
            if (empty($errors)) {
                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $ruta_archivo)) {
                    $foto_perfil_url = $ruta_archivo; // Guardamos la ruta para la base de datos
                } else {
                    $errors[] = "Hubo un error al guardar la foto de perfil.";
                }
            }
        }

        // --- Inserción en la base de datos (solo si no hubo errores) ---
        if (empty($errors)) {
            $sql = "INSERT INTO usuarios (nombre, dni, email, telefono, password_hash, es_refugio, foto_perfil_url, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";

            if ($stmt = $conexion->prepare($sql)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bind_param("sssssis", $nombre, $dni, $email, $telefono, $password_hash, $es_refugio, $foto_perfil_url);

                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit;
                } else {
                    $errors[] = "Algo salió mal al crear tu cuenta. Por favor, inténtalo de nuevo.";
                }
                $stmt->close();
            }
        }
    }
    
    // Si hubo algún error (de validación o de subida de archivo), se muestra aquí
    if (!empty($errors)) {
        // Mostrar errores
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error de Registro</title><link rel="stylesheet" href="estilos.css"></head><body>';
        echo '<div class="form-container">';
        echo '<h2>Error en el Registro</h2>';
        foreach ($errors as $error) {
            echo '<div class="alert">' . htmlspecialchars($error) . '</div>';
        }
        echo '<a href="registro.php" class="btn">Volver al formulario</a>';
        echo '</div>';
        echo '</body></html>';
    }

    $conexion->close();
}
?>