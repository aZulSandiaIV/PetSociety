<?php
session_start(); // Iniciamos sesión para saber si el usuario está logueado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar Animal Callejero - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1><a href="index.php">PetSociety</a></h1></div>
            <nav><ul><li><a href="index.php">Volver</a></li></ul></nav>
        </div>
    </header>
    <div class="form-container">
        <h2>Reportar Animal Callejero</h2>
        <p>¿Viste un animal que parece no tener hogar? Sube una foto y usa tu ubicación para marcarlo en el mapa y que otros puedan verlo.</p>
        
        <form action="procesar_avistamiento_mapa.php" method="post" enctype="multipart/form-data" id="form-avistamiento">
            <div class="form-group">
                <label>Foto del Animal</label>
                <input type="file" name="foto_avistamiento" accept="image/*" required>
            </div>
            <div class="form-group">
                <label>Descripción (opcional)</label>
                <textarea name="descripcion" rows="4" placeholder="Ej: Parecía asustado, llevaba un collar azul, estaba cerca del río..."></textarea>
            </div>

            <!-- Campos ocultos para las coordenadas -->
            <input type="hidden" name="latitud" id="latitud">
            <input type="hidden" name="longitud" id="longitud">

            <div class="form-group">
                <button type="button" id="obtener-ubicacion-btn" class="btn">1. Obtener mi Ubicación</button>
                <input type="submit" id="enviar-reporte-btn" class="btn" value="2. Enviar Reporte" disabled style="background-color: #ccc;">
            </div>
            <div id="mensaje-ubicacion" style="margin-top: 10px;"></div>
        </form>
    </div>

    <script src="Geolocalizacion.js"></script>
    <script>
        document.getElementById('obtener-ubicacion-btn').addEventListener('click', function() {
            const mensajeDiv = document.getElementById('mensaje-ubicacion');
            mensajeDiv.textContent = 'Buscando tu ubicación...';
            this.disabled = true;

            EXITO = function(position) {
                const { latitude, longitude } = position.coords;
                document.getElementById('latitud').value = latitude;
                document.getElementById('longitud').value = longitude;
                
                mensajeDiv.textContent = '¡Ubicación obtenida con éxito! Ya puedes enviar el reporte.';
                mensajeDiv.style.color = 'green';
                document.getElementById('enviar-reporte-btn').disabled = false;
                document.getElementById('enviar-reporte-btn').style.backgroundColor = '';
            };

            OBTENER_POSICION_ACTUAL();
        });
    </script>
</body>
</html>
