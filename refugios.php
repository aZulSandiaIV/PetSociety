<?php
session_start();
require_once "config.php";
require_once "funciones.php";

// --- Obtener todos los refugios usando la nueva función ---
$refugios = obtener_refugios($conexion);

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Refugios - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="refugio.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    
    <script src="js/CargaAsync.js"></script>
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
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="buzon.php">Mensajes</a></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li class="admin-panel-dropdown">
                                <span class="admin-panel-trigger">Panel de Administrador</span>
                                <div class="admin-submenu">
                                    <ul>
                                        <li><a href="admin/statistics.php">Estadísticas</a></li>
                                        <li><a href="admin/manage_publications.php">Administrar Publicaciones</a></li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="user-menu mobile-user-menu">
                            <span class="user-menu-trigger">
                                <span class="user-icon"></span>
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                            </span>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="mi_perfil.php">Mi Perfil</a></li>
                                    <li><a href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Refugios de Animales</h2>
        <p>Estos son los refugios que colaboran con nosotros. Puedes contactarlos o ver los animales que tienen en adopción.</p>

        <div id="refugios-container" class="refugios-container">
            <!-- Las tarjetas de refugios se cargarán aquí dinámicamente -->
            
        </div>
        
        <div>
            <button id="cargar-mas-btn" class="btn load-more-btn">Cargar Más</button>
        </div> 

    </div>

    
    <script src="js/refugios.js"></script>
    <script src="funciones_js.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Llama a la función para la interactividad de los menús
            interactividad_menus();
        });
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>
