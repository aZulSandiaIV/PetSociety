<?php
// Usar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once "config.php";
// --- Carga manual de PHPMailer ---
// Ajusta la ruta si tu carpeta de PHPMailer tiene un nombre diferente (ej. 'PHPMailer-master')
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$email = "";
$feedback_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $feedback_message = "Por favor, ingresa tu email.";
    } else {
        $email = trim($_POST["email"]);

        // Verificar si el email existe
        $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // El usuario existe, generar token y fecha de expiración
                $token = bin2hex(random_bytes(50));
                $token_expiry = date("Y-m-d H:i:s", time() + 3600); // Token válido por 1 hora

                // Guardar el token en la base de datos
                $sql_update = "UPDATE usuarios SET reset_token = ?, token_expiry = ? WHERE email = ?";
                if ($stmt_update = $conexion->prepare($sql_update)) {
                    $stmt_update->bind_param("sss", $token, $token_expiry, $email);
                    $stmt_update->execute();

                    // --- Enviar el correo con PHPMailer ---
                    $mail = new PHPMailer(true);
                    try {
                        // Configuración del servidor SMTP para Gmail
                        // $mail->SMTPDebug = 2; // Descomenta esta línea para ver el log de depuración
                        $mail->SMTPDebug = 0; // 0 para desactivar la depuración en producción
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'petsociety2026@gmail.com'; // Tu usuario SMTP
                        $mail->Password   = 'xotdwvfw pntvexga';   // Tu contraseña SMTP
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar STARTTLS para el puerto 587
                        $mail->Port       = 587;

                        // Remitente y Destinatario
                        $mail->setFrom('no-reply@petsociety.com', 'PetSociety');
                        $mail->addAddress($email);

                        // Contenido del correo
                        $mail->isHTML(true);
                        $mail->Subject = 'Restablece tu contraseña de PetSociety';
                        $reset_link = ROOT_URL . "restablecer_contraseña.php?token=" . $token;
                        $mail->Body    = "Hola,<br><br>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:<br><br><a href='{$reset_link}'>Restablecer Contraseña</a><br><br>Si no solicitaste esto, puedes ignorar este correo.<br><br>Saludos,<br>El equipo de PetSociety";
                        $mail->AltBody = "Hola,\n\nHemos recibido una solicitud para restablecer tu contraseña. Copia y pega el siguiente enlace en tu navegador para continuar:\n\n{$reset_link}\n\nSi no solicitaste esto, puedes ignorar este correo.\n\nSaludos,\nEl equipo de PetSociety";

                        $mail->send();
                        $feedback_message = "Revise su mail {$email}, en breve le estara llegando un link de recuperacion";

                    } catch (Exception $e) {
                        $feedback_message = "No se pudo enviar el mensaje. Error de Mailer: {$mail->ErrorInfo}";
                    }
                }
            } else {
                // Email no encontrado, mostramos el mismo mensaje por seguridad
                $feedback_message = "El mail {$email} no esta registrado.";
            }
            $stmt->close();
        }
    }
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Recuperar Contraseña</h2>
        <p>Ingresa tu dirección de email y te enviaremos un enlace para restablecer tu contraseña.</p>
        
        <?php if(!empty($feedback_message)): ?>
            <div class="alert" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?php echo htmlspecialchars($feedback_message); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Enviar Enlace de Recuperación">
            </div>
            <p><a href="login.php">Volver a Iniciar Sesión</a></p>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>