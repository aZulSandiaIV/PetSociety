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
    <title>Login - PetSociety</title>
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
                    <li><a href="refugios.php">Refugios</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <h2>Iniciar Sesión</h2>
        <p>Por favor, ingresa tus credenciales para continuar.</p>
        
        <!-- Mostrar mensaje de error si existe -->
        <?php if (isset($_SESSION['login_error'])): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: 500;">
                <?php 
                echo htmlspecialchars($_SESSION['login_error']); 
                unset($_SESSION['login_error']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="procesar_login.php" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo isset($_SESSION['login_email']) ? htmlspecialchars($_SESSION['login_email']) : ''; ?>" required>
                <?php if (isset($_SESSION['login_email'])) unset($_SESSION['login_email']); ?>
            </div>    
            <div class="password">

                <label>Contraseña</label>
                <div class="password">
                    <input id="password" type="password" name="password" required>
                    <button id='boton-ojito' type="button">ojito</button>
                </div>
                
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Login">
            </div>
            <p style="text-align: center;"><a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a></p>
            <p style="text-align: center;">¿No tienes una cuenta? <a href="registro.php">Regístrate ahora</a>.</p>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>

    <script src="funciones_js.js"></script>
    <script>
        // Activar funcionalidad del menú hamburguesa
        interactividad_menus();
    </script>
    <script>
        const passwordToggle = document.getElementById('boton-ojito');
        const password = document.getElementById('password');

        passwordToggle.addEventListener('click', function(){
            if(password.type == "password"){
                password.type = "text";
            }else{
                password.type = "password";
            }
        });

    </script>
</body>
</html>