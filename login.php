<?php
session_start();
// Si el usuario ya está logueado, redirigir al index.
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Iniciar Sesión</h2>
        <p>Por favor, ingresa tus credenciales para continuar.</p>
        <form action="procesar_login.php" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>    
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Login">
            </div>
            <p style="text-align: center;"><a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a></p>
            <p style="text-align: center;">¿No tienes una cuenta? <a href="registro.php">Regístrate ahora</a>.</p>
        </form>
    </div>
</body>
</html>