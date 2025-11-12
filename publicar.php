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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar un Animal - PetSociety</title>
    <!-- CSS de Leaflet para el mapa -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <!-- Estilo para el mapa -->
    <style>#mapa-seleccion { height: 400px; margin-top: 15px; border-radius: 8px; z-index: 9999; }</style>
</head>
<body>
    <?php include 'header_estandar.php'; ?>
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
                    <label>Zona / Barrio</label>
                    <input type="text" name="zona" placeholder="Ej: Palermo, Caballito, La Plata Centro" required>
                    <small>Escribe el barrio, localidad o una referencia simple para la búsqueda.</small>
                </div>
                <div class="form-group">
                    <label>Ubicación Exacta (Mapa)</label>
                    <input type="text" name="ubicacion_texto" id="ubicacion_texto" placeholder="Arrastra el marcador en el mapa o usa el botón" required readonly>
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
                <label>Edad Aproximada</label>
                <input type="text" name="edad" placeholder="Ej: Cachorro, 2 años, Senior">
            </div>
            <div class="form-group">
                <label>Tamaño</label>
                <select name="tamaño">
                    <option value="">-- No especificado --</option>
                    <option value="Pequeño">Pequeño</option>
                    <option value="Mediano">Mediano</option>
                    <option value="Grande">Grande</option>
                </select>
            </div>
            <div class="form-group">
                <label>Color Principal</label>
                <input type="text" name="color" placeholder="Ej: Negro, Marrón y blanco">
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
    <script src="Geolocalizacion.js"></script>
    <script src="funciones_js.js"></script>

    <script>        
        document.addEventListener('DOMContentLoaded', function() {
            // Llama a la función refactorizada para manejar el cambio de tipo de publicación
            manejarCambioTipoPublicacion();
            // --- LÓGICA DEL MAPA (REFACTORIZADA) ---
            const mapaInfo = inicializarMapaDeSeleccion('mapa-seleccion', 'latitud', 'longitud', 'ubicacion_texto');

            // Llama a la función refactorizada para el botón "Usar mi ubicación actual"
            usar_ubicacion_actual(mapaInfo);
        });
        
        // Llama a la función para la interactividad de los menús
        interactividad_menus();
    </script>
</body>
</html>