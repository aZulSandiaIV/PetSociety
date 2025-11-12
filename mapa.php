<?php
session_start(); // Asegurarse de que la sesión esté iniciada
require_once "config.php";

// --- LÓGICA PARA EL MAPA ---
// 1. Obtener todas las publicaciones activas (Adopción, Hogar Temporal).
$publicaciones = obtener_publicaciones($conexion);

// 2. Obtener los datos de avistamientos y perdidos.
$map_data = mapa_avistamientos($conexion, $publicaciones, 'mapa.php');
$avistamientos_json = $map_data['avistamientos_json'];
$perdidos_json = $map_data['perdidos_json'];

// 3. Preparar las publicaciones de adopción/hogar temporal para el mapa.
$publicaciones_json = mapa_publicaciones($publicaciones);

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Avistamientos - PetSociety</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <style>
        /* Estilos para que el mapa ocupe un buen espacio */
        #mapa-avistamientos {
            height: 600px; /* Altura del mapa */
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .map-section-container {
            padding-top: 20px;
            padding-bottom: 40px;
        }
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
                        <li><a href="mapa.php">Mapa</a></li>
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
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="mapa.php">Mapa</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
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

    <div class="container map-section-container">
        <h2>Mapa General de Avistamientos</h2>
        <p>Explora el mapa para ver los últimos avistamientos de animales callejeros y mascotas perdidas reportadas por la comunidad.</p>
        
        <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
            <button id="ver-mi-ubicacion" class="btn" style="width: auto;">Buscar por mi Zona</button>
            <a href="reportar_avistamiento_mapa.php" class="btn" style="width: auto; background-color: #7A9BA8;">Reportar Callejero</a>
        </div>
        <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
            <label for="rango-km" style="margin:0;">Rango (km):</label>
            <input id="rango-km" type="range" min="1" max="200" value="1" />
            <span id="rango-valor">1 km</span>
        </div>
        <div id="mapa-avistamientos"></div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="Geolocalizacion.js"></script>
    <script src="funciones_js.js"></script>
    <script>
        interactividad_menus();
        mapa_interactivo_index(<?php echo $avistamientos_json; ?>, <?php echo $perdidos_json; ?>, <?php echo $publicaciones_json; ?>);
    </script>

</body>
</html>