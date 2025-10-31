<?php
session_start();
$email_sugerido = isset($_SESSION["loggedin"]) && isset($_SESSION["email"]) ? $_SESSION["email"] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Contacto y Sugerencias</h2>
        <p>¿Tienes alguna idea o sugerencia para mejorar PetSociety? ¡Nos encantaría escucharla!</p>
        <form action="procesar_contacto.php" method="post">
            <div class="form-group"><label for="email">Tu Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_sugerido); ?>" required></div>
            <div class="form-group"><label for="comentario">Sugerencia o Comentario</label><textarea id="comentario" name="comentario" rows="8" required placeholder="Escribe tu mensaje aquí..."></textarea></div>
            <div class="form-group"><input type="submit" class="btn" value="Enviar Comentario"></div>
            <a href="index.php">Volver al inicio</a>
        </form>
    </div>
</body>
</html>
