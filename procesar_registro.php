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
        $sql = "INSERT INTO usuarios (nombre, dni, email, telefono, password_hash, es_refugio, is_admin) VALUES (?, ?, ?, ?, ?, ?, 0)";

        if ($stmt = $conexion->prepare($sql)) {
            // Hashear la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt->bind_param("sssssi", $nombre, $dni, $email, $telefono, $password_hash, $es_refugio);

            if ($stmt->execute()) {
                // Redirigir a la página de login con un mensaje de éxito
                echo "¡Registro exitoso! Serás redirigido a la página de inicio de sesión en 3 segundos.";
                header("refresh:3;url=login.php");
                exit();
            } else {
                echo "Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            }
            $stmt->close();
        }
    } else {
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