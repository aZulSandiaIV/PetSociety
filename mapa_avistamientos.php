<?php
require_once "config.php";

// Obtener los últimos 50 avistamientos (para no sobrecargar el mapa)
$sql = "SELECT latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
        FROM avistamientos 
        ORDER BY fecha_avistamiento DESC 
        LIMIT 50";

$avistamientos_json = "[]";
if ($result = $conexion->query($sql)) {
    $avistamientos = [];
    while ($row = $result->fetch_assoc()) {
        // Preparamos los datos para el popup del mapa
        $row['popup_html'] = "
            <div>
                <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Avistamiento' style='width:150px; height:auto; border-radius:4px;'>
                <p>" . htmlspecialchars($row['descripcion']) . "</p>
                <small>Visto el: " . date('d/m/Y H:i', strtotime($row['fecha_avistamiento'])) . "</small>
            </div>
        ";
        $avistamientos[] = $row;
    }
    $avistamientos_json = json_encode($avistamientos);
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Avistamientos - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        #mapa { height: 600px; width: 100%; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1><a href="index.php">PetSociety</a></h1></div>
            <nav><ul><li><a href="index.php">Inicio</a></li><li><a href="reportar_avistamiento_mapa.php" class="btn" style="color:white;padding:5px 10px;">Reportar Avistamiento</a></li></ul></nav>
        </div>
    </header>

    <div class="container">
        <h2>Mapa de Avistamientos Recientes</h2>
        <p>Estos son los últimos avistamientos reportados. Haz clic en un marcador para ver los detalles o usa el botón para ver tu posición.</p>
        <button id="ver-mi-ubicacion" class="btn" style="margin-bottom: 15px; width: auto;">Mostrar mi ubicación</button>
        <div id="mapa"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Incluimos tu script de geolocalización -->
    <script src="geolocalizacion.js"></script>

    <script>
        // --- Lógica del Mapa ---
        // Inicializa el mapa centrado en una ubicación genérica
        const mapa = L.map('mapa').setView([-34.60, -58.38], 12); // Buenos Aires como ejemplo

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);

        const avistamientos = <?php echo $avistamientos_json; ?>;

        avistamientos.forEach(avistamiento => {
            L.marker([avistamiento.latitud, avistamiento.longitud])
                .addTo(mapa)
                .bindPopup(avistamiento.popup_html);
        });

        // --- Lógica para mostrar la ubicación del usuario ---
        document.getElementById('ver-mi-ubicacion').addEventListener('click', function() {
            this.textContent = 'Buscando...';
            this.disabled = true;
            
            // Definimos la función que se ejecutará cuando se obtenga la ubicación
            const miCallbackDeExito = function(position) {
                ponerEnMapa(position.coords.latitude, position.coords.longitude, mapa);
            };

            geolocalizador.obtenerPosicionActual(miCallbackDeExito);
        });

    </script>
</body>
</html>