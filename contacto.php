<?php
session_start();
$email_sugerido = (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["email"])) ? $_SESSION["email"] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="refugios.php">Refugios</a></li>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li><a href="buzon.php">Mensajes</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

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
</body>
</html>
