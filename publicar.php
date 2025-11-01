<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar un Animal - PetSociety</title>
    <!-- CSS de Leaflet para el mapa -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <!-- Estilo para el mapa -->
    <style>#mapa-seleccion { height: 400px; margin-top: 15px; border-radius: 8px; z-index: 1; }</style>
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
        <h2>Datos de la Publicación</h2>
        <form action="procesar_publicacion.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Título de la Publicación</label>
                <input type="text" name="titulo" required>
            </div>
            <div class="form-group">
                <label>Tipo de Publicación</label>
                <select name="tipo_publicacion" id="tipo_publicacion" required>
                    <option value="Adopción">Dar en Adopción</option>
                    <option value="Hogar Temporal">Buscar Hogar Temporal</option>
                    <option value="Perdido">Reportar como Perdido</option>
                </select>
            </div>

            <div class="form-group">
                <label>Descripción de la Publicación</label>
                <textarea name="contenido" rows="5" required></textarea>
            </div>

            <div id="campos_ubicacion" style="display: block;"> <!-- Cambiado para ser visible por defecto -->
                <div class="form-group">
                    <label>Ubicación del Animal</label>
                    <input type="text" name="ubicacion_texto" placeholder="Ej: Cerca del parque central, Calle Falsa 123" required>
                    <button type="button" id="usar-ubicacion-actual" class="btn" style="width: auto; margin-top: 5px; background-color: #97BC62;">Usar mi ubicación actual</button>
                    <!-- Campos ocultos para las coordenadas -->
                    <input type="hidden" name="latitud" id="latitud">
                    <input type="hidden" name="longitud" id="longitud">
                </div>
                <!-- Contenedor para el mapa interactivo -->
                <div id="mapa-seleccion"></div>
                <label id="label_caracteristicas">Características distintivas (opcional)</label>
                <textarea name="caracteristicas_distintivas" rows="3" placeholder="Ej: Collar rojo, una mancha en el ojo derecho, cojea un poco"></textarea>
            </div>
            
            <h2>Datos del Animal</h2>
            <div class="form-group">
                <label>Nombre del Animal</label>
                <input type="text" name="nombre_animal" required>
            </div>
            <div class="form-group">
                <label>Especie</label>
                <input type="text" name="especie" placeholder="Ej: Perro, Gato" required>
            </div>
            <div class="form-group">
                <label>Raza</label>
                <input type="text" name="raza" placeholder="Ej: Mestizo, Labrador">
            </div>
            <div class="form-group">
                <label>Género</label>
                <select name="genero" required>
                    <option value="Macho">Macho</option>
                    <option value="Hembra">Hembra</option>
                </select>
            </div>
            <div class="form-group">
                <label>Foto del Animal</label>
                <input type="file" name="foto_animal" accept="image/jpeg, image/png, image/gif">
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Crear Publicación">
            </div>
        </form>
    </div>

    <!-- Incluimos Leaflet y tu script de geolocalización -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="geolocalizacion.js"></script>

    <script>
        document.getElementById('tipo_publicacion').addEventListener('change', function() {
            const camposUbicacion = document.getElementById('campos_ubicacion');
            const ubicacionInput = camposUbicacion.querySelector('input[name="ubicacion_texto"]');
            const labelCaracteristicas = document.getElementById('label_caracteristicas');

            // Los campos de ubicación siempre están visibles ahora.
            // Solo cambiamos el texto de la etiqueta si es un animal perdido.
            labelCaracteristicas.textContent = (this.value === 'Perdido') 
                ? 'Características distintivas (opcional)' 
                : 'Notas adicionales sobre la ubicación (opcional)';
        });

        // --- LÓGICA DEL MAPA INTERACTIVO ---
        const latitudInput = document.getElementById('latitud');
        const longitudInput = document.getElementById('longitud');
        const ubicacionTextoInput = document.querySelector('input[name="ubicacion_texto"]');

        // 1. Inicializar el mapa
        const mapaSeleccion = L.map('mapa-seleccion').setView([-34.60, -58.38], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapaSeleccion);

        // 2. Crear un marcador arrastrable
        let marcador = L.marker(mapaSeleccion.getCenter(), { draggable: true }).addTo(mapaSeleccion);
        marcador.bindPopup("Arrastra este marcador a la ubicación del animal.").openPopup();

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

        // Inicializar campos con la posición inicial del marcador
        const posInicial = marcador.getLatLng();
        actualizarCampos(posInicial.lat, posInicial.lng);

        // Lógica para el botón "Usar mi ubicación actual"
        document.getElementById('usar-ubicacion-actual').addEventListener('click', function() {
            this.textContent = 'Obteniendo...';
            this.disabled = true;

            // Sobrescribimos la función EXITO para rellenar los campos del formulario
            EXITO = function(position) {
                const { latitude, longitude } = position.coords;
                // Mover el mapa y el marcador a la nueva posición
                mapaSeleccion.setView([latitude, longitude], 16);
                marcador.setLatLng([latitude, longitude]);
                document.getElementById('usar-ubicacion-actual').textContent = '¡Ubicación Obtenida!';
                actualizarCampos(latitude, longitude); // Actualizar todos los campos
            };
            OBTENER_POSICION_ACTUAL();
        })
    </script>
</body>
</html>