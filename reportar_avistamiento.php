<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

if (!isset($_GET['id_animal']) || empty($_GET['id_animal'])) {
    die("Error: No se especificó un animal.");
}
$id_animal = intval($_GET['id_animal']);

// Usar la nueva función para obtener el nombre del animal
$nombre_animal = obtener_nombre_animal($conexion, $id_animal);

if ($nombre_animal === null) {
    die("Error: Animal no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Avistamiento de <?php echo htmlspecialchars($nombre_animal); ?></title>
    <!-- CSS de Leaflet para el mapa -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <!-- Estilo para el mapa -->
    <style>#mapa-seleccion { height: 350px; margin-top: 15px; border-radius: 8px; z-index: 1; }</style>
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

    <div class="form-container">
        <h2>Reportar Avistamiento de "<?php echo htmlspecialchars($nombre_animal); ?>"</h2>
        <p>Si crees que has visto a esta mascota, por favor, completa los siguientes datos para ayudar a su dueño.</p>
        <form action="procesar_avistamiento.php" method="post" id="form-avistamiento">
            <input type="hidden" name="id_animal" value="<?php echo $id_animal; ?>">
            <div class="form-group">
                <label>Última ubicación donde fue visto</label>
                <input type="text" name="ultima_ubicacion_vista" id="ubicacion_texto" placeholder="Arrastra el marcador en el mapa o usa el botón" required readonly>
                <button type="button" id="usar-ubicacion-actual" class="btn" style="width: auto; margin-top: 5px; background-color: #97BC62;">Usar mi ubicación actual</button>
                <!-- Campos ocultos para las coordenadas -->
                <input type="hidden" name="latitud" id="latitud">
                <input type="hidden" name="longitud" id="longitud">
            </div>
            <!-- Contenedor para el mapa interactivo -->
            <div id="mapa-seleccion"></div>
            <div class="form-group">
                <label>Características distintivas o estado del animal (opcional)</label>
                <textarea name="caracteristicas_distintivas" rows="4" placeholder="Ej: Llevaba un collar rojo, parecía asustado, cojeaba un poco..."></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Enviar Reporte de Avistamiento">
            </div>
            <a href="index.php">Cancelar y volver</a>
        </form>
    </div>

    <!-- Incluimos Leaflet y tu script de geolocalización -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="Geolocalizacion.js"></script>
    <script src="funciones_js.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el mapa de selección
            const mapaInfo = inicializarMapaDeSeleccion('mapa-seleccion', 'latitud', 'longitud', 'ubicacion_texto');
            
            // Activar el botón de "Usar mi ubicación actual"
            usar_ubicacion_actual(mapaInfo);
            
            // Activar la interactividad de los menús
            interactividad_menus();
        });
    </script>
</body>
</html>