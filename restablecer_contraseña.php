<?php
require_once "config.php";

$token = $_GET['token'] ?? '';
$password = $confirm_password = "";
$feedback_message = "";
$error_message = "";
$show_form = false;

// 1. Validar que el token exista en la URL
if (empty($token)) {
    $error_message = "Token no proporcionado o inválido. Por favor, utiliza el enlace que te enviamos por correo.";
} else {
    // 2. Buscar el token en la base de datos
    $sql = "SELECT id_usuario, token_expiry FROM usuarios WHERE reset_token = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id_usuario, $token_expiry);
            $stmt->fetch();

            // 3. Verificar que el token no haya expirado
            if (strtotime($token_expiry) > time()) {
                $show_form = true; // El token es válido, podemos mostrar el formulario
            } else {
                $error_message = "El enlace de restablecimiento ha expirado. Por favor, solicita uno nuevo.";
                // Opcional: Limpiar el token expirado de la BD
                $sql_clear = "UPDATE usuarios SET reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
                if ($stmt_clear = $conexion->prepare($sql_clear)) {
                    $stmt_clear->bind_param("s", $token);
                    $stmt_clear->execute();
                    $stmt_clear->close();
                }
            }
        } else {
            $error_message = "El enlace de restablecimiento no es válido o ya ha sido utilizado.";
        }
        $stmt->close();
    }
}

// 4. Procesar el formulario cuando se envía con la nueva contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && $show_form) {
    // Validaciones de la nueva contraseña
    if (empty(trim($_POST['password']))) {
        $error_message = "Por favor, ingresa una nueva contraseña.";
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $error_message = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty(trim($_POST['confirm_password']))) {
        $error_message = "Por favor, confirma la contraseña.";
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($error_message) && ($password != $confirm_password)) {
            $error_message = "Las contraseñas no coinciden.";
        }
    }

    // Si no hay errores, actualizar la contraseña
    if (empty($error_message)) {
        $sql_update = "UPDATE usuarios SET password_hash = ?, reset_token = NULL, token_expiry = NULL WHERE id_usuario = ?";
        if ($stmt_update = $conexion->prepare($sql_update)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_update->bind_param("si", $password_hash, $id_usuario);

            if ($stmt_update->execute()) {
                $feedback_message = "¡Tu contraseña ha sido actualizada con éxito! Serás redirigido a la página de inicio de sesión en 5 segundos.";
                header("refresh:5;url=login.php");
                $show_form = false; // Ocultar el formulario después del éxito
            } else {
                $error_message = "Algo salió mal. Por favor, inténtalo de nuevo.";
            }
            $stmt_update->close();
        }
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Restablecer Contraseña</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <p><a href="recuperar_contraseña.php">Solicitar un nuevo enlace</a></p>
        <?php elseif (!empty($feedback_message)): ?>
            <div class="alert" style="background-color: #d4edda; color: #155724;"><?php echo htmlspecialchars($feedback_message); ?></div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <p>Ingresa tu nueva contraseña a continuación.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . htmlspecialchars($token); ?>" method="post">
                <div class="form-group"><label>Nueva Contraseña</label><input type="password" name="password" required minlength="6"></div>
                <div class="form-group"><label>Confirmar Nueva Contraseña</label><input type="password" name="confirm_password" required minlength="6"></div>
                <div class="form-group"><input type="submit" class="btn" value="Cambiar Contraseña"></div>
            </form>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>