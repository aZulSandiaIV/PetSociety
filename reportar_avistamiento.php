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

// Obtener nombre del animal para mostrarlo
$sql_animal = "SELECT nombre FROM animales WHERE id_animal = ?";
$nombre_animal = '';
if ($stmt = $conexion->prepare($sql_animal)) {
    $stmt->bind_param("i", $id_animal);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nombre_animal = $row['nombre'];
    } else {
        die("Animal no encontrado.");
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar Avistamiento de <?php echo htmlspecialchars($nombre_animal); ?></title>
    <!-- CSS de Leaflet para el mapa -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
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
                <ul>
                    <li>Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                    <?php endif; ?>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="refugios.php">Refugios</a></li>
                    <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
                    <li><a href="buzon.php">Buzón</a></li>
                    <li><a href="publicar.php">Publicar Animal</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
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
                <input type="text" name="ultima_ubicacion_vista" placeholder="Arrastra el marcador en el mapa o usa el botón" required>
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
    <script src="geolocalizacion.js"></script>

    <script>
        // --- LÓGICA DEL MAPA INTERACTIVO ---
        const latitudInput = document.getElementById('latitud');
        const longitudInput = document.getElementById('longitud');
        const ubicacionTextoInput = document.querySelector('input[name="ultima_ubicacion_vista"]');

        // 1. Inicializar el mapa
        const mapaSeleccion = L.map('mapa-seleccion').setView([-34.60, -58.38], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapaSeleccion);

        // 2. Crear un marcador arrastrable
        let marcador = L.marker(mapaSeleccion.getCenter(), { draggable: true }).addTo(mapaSeleccion);
        marcador.bindPopup("Arrastra este marcador al lugar del avistamiento.").openPopup();

        // Función para actualizar campos y dirección
        function actualizarCampos(lat, lng) {
            latitudInput.value = lat.toFixed(8);
            longitudInput.value = lng.toFixed(8);
            
            // Geocodificación inversa para obtener la dirección
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        ubicacionTextoInput.value = data.display_name;
                    } else {
                        ubicacionTextoInput.value = `Ubicación: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    }
                })
                .catch(err => {
                    console.error("Error en geocodificación inversa:", err);
                    ubicacionTextoInput.value = `Ubicación: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                });
        }

        // 3. Actualizar campos cuando se arrastra el marcador
        marcador.on('dragend', function(e) {
            const latlng = e.target.getLatLng();
            actualizarCampos(latlng.lat, latlng.lng);
        });

        // Lógica para el botón "Usar mi ubicación actual"
        document.getElementById('usar-ubicacion-actual').addEventListener('click', function() {
            this.textContent = 'Obteniendo...';
            this.disabled = true;

            EXITO = function(position) {
                const { latitude, longitude } = position.coords;
                mapaSeleccion.setView([latitude, longitude], 16);
                marcador.setLatLng([latitude, longitude]);
                document.getElementById('usar-ubicacion-actual').textContent = '¡Ubicación Obtenida!';
                actualizarCampos(latitude, longitude);
            };
            OBTENER_POSICION_ACTUAL();
        });
    </script>
</body>
</html>