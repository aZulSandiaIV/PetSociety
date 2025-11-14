<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a PetSociety</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="index.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
</head>
<body>

    <?php include 'header_estandar.php'; ?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Estamos aqui para ayudarte a encontrar tu mascota</h1>
            <div class="hero-buttons">
                <a href="publicar.php?estado=Perdido" class="hero-btn perdiste">Perdí a mi Mascota</a>
                <a href="reportar_avistamiento_mapa.php" class="hero-btn encontraste">Encontré una Mascota perdida</a>
            </div>
        </div>
        <div class="hero-image-wrapper">
            <img src="img/hero-image.jpg" alt="Mascotas">
        </div>
    </div>
</div>

    <div class="container">
        <div id="seccion-publicaciones">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                <h2>Encuentra a tu próximo compañero</h2>
                <button id="toggle-filters-btn" class="btn-toggle-filters">Ocultar Filtros</button>
            </div>
        </div>

        <div class="main-content-wrapper">
            <!-- INICIO DE LA BARRA LATERAL DE FILTROS -->
            <aside class="filters-sidebar">
                <div class="filters-header">
                    <h3>Filtros</h3>
                    <a href="index.php#seccion-publicaciones" class="clear-filters-btn">Limpiar</a>
                </div>

                <div class="filter-group">
                    <input type="text" name="search" id="search-filter" class="form-group" placeholder="Buscar por palabra clave..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                </div>

                <div class="filter-group">
                    <h4>Estado</h4>
                    <label for="estado-adopcion"><input id="estado-adopcion" type="radio" name="estado" value="Adopción"> En Adopción</label>
                    <label for="estado-temporal"><input id="estado-temporal" type="radio" name="estado" value="Hogar Temporal"> Hogar Temporal</label>
                    <label for="estado-perdido"><input id="estado-perdido" type="radio" name="estado" value="Perdido"> Perdido</label>
                </div>

                <div class="filter-group">
                    <h4>Especie</h4>
                    <label for="especie-perro"><input id="especie-perro" type="radio" name="especie" value="Perro"> Perro</label>
                    <label for="especie-gato"><input id="especie-gato" type="radio" name="especie" value="Gato"> Gato</label>
                    <label for="especie-otro"><input id="especie-otro" type="radio" name="especie" value="Otro"> Otro</label>
                </div>

                <div class="filter-group">
                    <h4>Tamaño</h4>
                    <label for="tamano-peq"><input id="tamano-peq" type="radio" name="tamano" value="Pequeño"> Pequeño</label>
                    <label for="tamano-med"><input id="tamano-med" type="radio" name="tamano" value="Mediano"> Mediano</label>
                    <label for="tamano-grande"><input id="tamano-grande" type="radio" name="tamano" value="Grande"> Grande</label>
                </div>

                <button type="button" id="apply-filters" class="btn">Aplicar filtros</button>
                
            </aside>
        <div class="feed-wrapper content-main"> <!-- Usamos un nuevo wrapper para el feed y el botón -->
            <div class="feed-container"></div>

            <div class="btn-container">
                <button id="cargar-mas" class="btn">Cargar Más</button> 
            </div>
            
        </div>

    <!-- MODAL PARA VER DETALLES DEL ANIMAL -->
    <div id="modal-detalles" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
            <div class="modal-body">
                <img id="modal-imagen" src="" alt="Foto del animal" class="modal-img">
                <div class="modal-info">
                    <h2 id="modal-titulo"></h2>
                    <p><strong>Nombre:</strong> <span id="modal-nombre"></span></p>
                    <p><strong>Especie:</strong> <span id="modal-especie"></span></p>
                    <p><strong>Raza:</strong> <span id="modal-raza"></span></p>
                    <p><strong>Género:</strong> <span id="modal-genero"></span></p>
                    <p><strong>Edad:</strong> <span id="modal-edad"></span></p>
                    <p><strong>Tamaño:</strong> <span id="modal-tamano"></span></p>
                    <p><strong>Color:</strong> <span id="modal-color"></span></p>
                    <hr>
                    <h3>Descripción</h3>
                    <p id="modal-descripcion"></p>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: #fff; padding: 30px; border-radius: 10px; max-width: 800px; width: 90%; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-close { position: absolute; top: 10px; right: 15px; font-size: 28px; font-weight: bold; border: none; background: none; cursor: pointer; }
        .modal-body { display: flex; gap: 20px; }
        .modal-img { width: 300px; height: 300px; object-fit: cover; border-radius: 8px; }
        .modal-info h2 { margin-top: 0; }
        .modal-info p { margin: 8px 0; }
        @media (max-width: 768px) {
            .modal-body { flex-direction: column; }
            .modal-img { width: 100%; height: 250px; }
        }
    </style>

    <script>
    // Cerrar el modal si se hace clic fuera de él
    window.onclick = function(event) {
        const modal = document.getElementById('modal-detalles');
        if (event.target == modal) {
            cerrarModal();
        }
    }
    </script>
    
</div> <!-- Cierre de .container -->


    <?php include 'footer.php'; ?>

    <!-- Scripts para el mapa -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="Geolocalizacion.js"></script>

    <script src="js/CargaAsync.js"></script>
    <script src="funciones_js.js"></script>
    <script src="js/index.js"></script>
    
    <script>
        // --- Lógica del Mapa ---
        // Llama a la función para la interactividad de los menús
        interactividad_menus();
        
        // Llama a la función para ocultar/mostrar filtros
        ocultar_mostrar_filtros();

        // Llama a la nueva función para inicializar el mapa

        // Si hay un hash en la URL, hacer scroll a esa sección
        window.addEventListener('load', function() {
            if (window.location.hash === '#seccion-publicaciones') {
                document.getElementById('seccion-publicaciones').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    </script>

</body>
</html>