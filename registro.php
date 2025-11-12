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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-menu">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="mapa.php">Mapa</a></li>
                    <li><a href="refugios.php">Refugios</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <h2>Crear una Cuenta</h2>
        <p>Completa este formulario para registrarte.</p>
        <form action="procesar_registro.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>DNI</label>
                <input type="text" name="dni" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Teléfono (Opcional)</label>
                <input type="text" name="telefono">
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <div>
                    <input id="password" type="password" name="password" required minlength="6">       
                    <button id="passwordToggle" type="button">ojito</button>             
                </div>
            </div>
            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <div>
                    <input id="passwordConfirm" type="password" name="confirm_password" required minlenght="6">
                    <button id="secondPasswordToggle" type="button">ojito</button>   
                </div>
            </div>
            <div class="form-group">
                <label>Foto de Perfil (Opcional)</label>
                <input type="file" name="foto_perfil" accept="image/jpeg, image/png, image/gif">
                <small>Sube una imagen para tu perfil. No es obligatorio.</small>
            </div>
            <div class="form-group">
                <label for="es_refugio" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="es_refugio" id="es_refugio" value="1" style="width: auto;"> Registrarse como Refugio
                </label>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Registrarse">
            </div>
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>

    <script src="funciones_estilos.js"></script>
    <script src="funciones_js.js"></script>
    <script>
        // Activar funcionalidad del menú hamburguesa
        interactividad_menus();
        // Asociar los botones con sus respectivos inputs
        asociarToggleContraseña('passwordToggle', 'password');
        asociarToggleContraseña('secondPasswordToggle', 'passwordConfirm');
    </script>
</body>
</html>