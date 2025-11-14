<?php
session_start(); // Iniciamos sesión para saber si el usuario está logueado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Animal Callejero - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
</head>
<body>
    <?php include 'header_estandar.php'; ?>
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

            <div class="btn-container">
                <button type="button" id="obtener-ubicacion-btn" class="btn">1. Obtener mi Ubicación</button>
                <input type="submit" id="enviar-reporte-btn" class="btn" value="2. Enviar Reporte" disabled style="background-color: #ccc;">
            </div>
            <div id="mensaje-ubicacion" style="margin-top: 10px;"></div>
        </form>
    </div>

    <!-- Incluimos Leaflet y tus scripts de geolocalización y funciones -->
    <script src="Geolocalizacion.js"></script>
    <script src="funciones_js.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Llama a la función refactorizada para manejar la obtención de la ubicación
            obtener_ubicacion_para_formulario('obtener-ubicacion-btn', 'latitud', 'longitud', 'enviar-reporte-btn', 'mensaje-ubicacion');
            
            // Llama a la función para la interactividad de los menús
            interactividad_menus();
        });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>
