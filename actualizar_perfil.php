<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    
    // Validaciones básicas
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    }
    
    if (empty($email)) {
        $errores[] = "El email es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido.";
    }
    
    // Verificar si el email ya existe para otro usuario
    if (empty($errores)) {
        $sql_check = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
        if ($stmt_check = $conexion->prepare($sql_check)) {
            $stmt_check->bind_param("si", $email, $id_usuario);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $errores[] = "El email ya está en uso por otro usuario.";
            }
            $stmt_check->close();
        }
    }
    
    // Si no hay errores, actualizar
    if (empty($errores)) {
        $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ? WHERE id_usuario = ?";
        if ($stmt_update = $conexion->prepare($sql_update)) {
            $stmt_update->bind_param("sssi", $nombre, $email, $telefono, $id_usuario);
            if ($stmt_update->execute()) {
                $_SESSION['mensaje'] = "Perfil actualizado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar el perfil.";
                $_SESSION['tipo_mensaje'] = "error";
            }
            $stmt_update->close();
        }
    } else {
        $_SESSION['mensaje'] = implode(" ", $errores);
        $_SESSION['tipo_mensaje'] = "error";
    }
    
    $conexion->close();
}

header("location: mis_publicaciones.php");
exit;
?>