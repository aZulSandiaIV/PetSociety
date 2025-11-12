<?php
session_start();
$email_sugerido = (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["email"])) ? $_SESSION["email"] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
</head>
<body>
    <?php include 'header_estandar.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-envelope"></i> Contacto y Sugerencias</h2>
            <p>¿Tienes alguna idea, sugerencia o necesitas ayuda? ¡Nos encantaría escucharla! Tu retroalimentación es muy valiosa para nosotros.</p>
            <form action="procesar_contacto.php" method="post">
                <div class="form-group"><label for="email">Tu Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_sugerido); ?>" required></div>
                <div class="form-group"><label for="comentario">Sugerencia o Comentario</label><textarea id="comentario" name="comentario" rows="8" required placeholder="Escribe tu mensaje aquí... Puedes contarnos sobre errores, mejoras, historias exitosas o cualquier idea que tengas."></textarea></div>
                <div class="form-group"><input type="submit" class="btn" value="Enviar Comentario"></div>
                <a href="index.php" class="btn" style="background-color: #8c8c8c; display: inline-block; margin-top: 10px;">Volver al inicio</a>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="funciones_js.js"></script>
    <script>
        // Activar funcionalidad del menú hamburguesa
        interactividad_menus();
    </script>
</body>
</html>
