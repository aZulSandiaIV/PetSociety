<?php
session_start();
require_once "config.php";

// --- LÓGICA PARA EL MAPA (replicando la del index.php) ---

// 1. Obtener todas las publicaciones activas para los marcadores de adopción/refugio.
$publicaciones = obtener_publicaciones($conexion, []);

// 2. Obtener los datos de avistamientos y perdidos.
$map_data = mapa_avistamientos($conexion, $publicaciones);
$avistamientos_json = $map_data['avistamientos_json'];
$perdidos_json = $map_data['perdidos_json'];

// 3. Preparar los datos de las publicaciones para el mapa.
$publicaciones_json = mapa_publicaciones($publicaciones);

$conexion->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Avistamientos - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css"> 
    <?php endif; ?> 
    <style>
        #mapa-avistamientos { height: 600px; width: 100%; }
    </style>
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
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                        <li><a href="admin/statistics.php" class="admin-panel-link">Panel Admin</a></li>
                                    <?php endif; ?>
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
        <h2>Mapa Completo</h2>
        <p>Explora el mapa para ver todas las publicaciones: animales en adopción , refugios , mascotas perdidas  y avistamientos de callejeros.</p>
        <button id="ver-mi-ubicacion" class="btn" style="margin-bottom: 15px; width: auto;">Mostrar mi ubicación</button>
        <div id="mapa-avistamientos"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Incluimos tu script de geolocalización -->
     <script src="Geolocalizacion.js"></script>
    <!-- Incluimos el script de funciones JS para el menú -->
    <script src="funciones_js.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Llama a la función del mapa interactivo, pasándole los datos.
            // Como esta página no muestra publicaciones, se pasa un array vacío [].
            mapa_interactivo_index(
                <?php echo $avistamientos_json; ?>,
                <?php echo $perdidos_json; ?>,
                <?php echo $publicaciones_json; ?>
            );

            // Llama a la función para la interactividad de los menús
            interactividad_menus();
        });
    </script>
</body>
</html>