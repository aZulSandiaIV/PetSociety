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
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <style>
        /* Estilos específicos para el feed de animales */
        .feed-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .animal-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden; /* Para que la imagen no se salga de los bordes redondeados */
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease-in-out;
            font-family: 'Inter', sans-serif;
        }
        .animal-card:hover {
            transform: translateY(-5px);
        }
        .animal-card img {
            width: 100%;
            height: 200px;
            object-fit: cover; /* Asegura que la imagen cubra el espacio sin deformarse */
        }
        .animal-card-content {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.95);
        }
        .animal-card h3 {
            color: #404040;
            font-size: 1.3rem;
            margin-bottom: 1px;
            font-weight: bold;
        }
        .animal-card p {
            color: #595959;
            font-size: 0.95rem;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .animal-card .details {
            color: #8C8C8C;
            font-size: 0.9rem;
        }
        .animal-card .contact-btn {
            margin-top: auto;
            background-color: #6B8E9F;
            text-align: center;
        }
        .animal-card .details-btn {
            margin-top: 8px;
            background-color: #f0f0f0;
            color: #404040;
            text-align: center;
        }
        .animal-card .details-btn:hover {
            background-color: #e0e0e0;
            color: #202020;
        }
        .animal-card .report-btn {
            margin-top: auto;
            background-color: #A68A6B;
            text-align: center;
        }
        .animal-card .own-post-indicator {
            margin-top: auto;
            background-color: #D9D9D9;
            cursor: default;
        }
        .refugio-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #404040;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75em;
        }
        /* Estilo para el mapa */
        #mapa-avistamientos {
            height: 500px; width: 100%; margin-bottom: 30px; border-radius: 8px; z-index: 20;
        }

        .hero-section {
                background-color: #F2F2F2;
                padding: 80px 0;
        }
        .hero-section .container {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 60px;
                width: 80%;
        }
        .hero-content {
                flex: 1;
                max-width: 600px;
        }
        .hero-content h1 {
                color: #404040;
                font-size: 2.5rem;
                margin-bottom: 30px;
                font-weight: bold;
                line-height: 1.2;
        }
        .hero-buttons {
                display: flex;
                gap: 20px;
        }
        .hero-btn {
                padding: 10px 24px;
                color: white;
                text-decoration: none;
                font-size: 1.1rem;
                font-weight: 600;
                border-radius: 50px;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-align: center;
        }
        .hero-btn.perdiste {
                background-color: #A68A6B;
        }
        .hero-btn.perdiste:hover {
                background-color: #8F7354;
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .hero-btn.encontraste {
                background-color: #6B8E9F;
        }
        .hero-btn.encontraste:hover {
                background-color: #5A7A8A;
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .hero-image-wrapper {
                flex: 1;
                max-width: 500px;
                display: flex;
                justify-content: center;
                align-items: center;
        }
        .hero-image-wrapper img {
                width: 100%;
                max-width: 400px;
                height: 400px;
                object-fit: cover;
                border-radius: 50%;
        }
        @media (max-width: 1400px) {
                .hero-buttons {
                        flex-direction: column;
                        gap: 15px;
                }
                .hero-btn {
                        max-width: 300px;
                        width: 100%;
                }
        }
        @media (max-width: 768px) {
                .hero-section .container {
                        flex-direction: column !important;
                        gap: 40px !important;
                }
                .hero-content {
                        max-width: 100% !important;
                        text-align: center !important;
                }
                .hero-content h1 {
                        font-size: 2rem !important;
                }
                .hero-buttons {
                        flex-direction: column !important;
                        gap: 15px !important;
                        align-items: center !important;
                }
                .hero-image-wrapper {
                        max-width: 100% !important;
                }
                .hero-image-wrapper img {
                        max-width: 300px !important;
                        height: 300px !important;
                }
        }
         h1, h2, h3, h4, h5, h6
         {
             font-family: 'Inter', sans-serif;
         }

        button, .btn, a.button
        {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
        }
        
        
        header nav ul.nav-menu li {
            white-space: nowrap;
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
                        <!-- DEBUG: Admin status - Remover después de probar -->
                        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <!-- Usuario: <?php echo htmlspecialchars($_SESSION["nombre"] ?? 'No definido'); ?> | Admin: <?php echo isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'No definido'; ?> -->
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
            <h2>Encuentra a tu próximo compañero</h2>
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
        <div>
            <div class="feed-container">
                <!-- Las publicaciones se cargarán aquí dinámicamente -->
            </div>
            <button id="cargar-mas" class="btn">Cargar Más</button>
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
    
    </div> <!-- Cierre de .main-content-wrapper -->

        <!-- SECCIÓN DEL MAPA DE AVISTAMIENTOS -->
        <div style="margin-bottom: 30px; margin-top: 40px;">
            <h2>¿Has visto a un animal perdido?</h2>
            <p>Explora el mapa para ver los últimos avistamientos y animales perdidos en tu zona. Cada marcador te muestra información importante para ayudar a reunirlos con sus familias.</p>
            <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                <button id="ver-mi-ubicacion" class="btn" style="width: auto;">Mostrar mi ubicación</button>
                <a href="reportar_avistamiento_mapa.php" class="btn" style="width: auto; background-color: #7A9BA8;">Reportar Callejero</a>
                <a href="mapa_avistamientos.php" class="btn" style="width: auto; background-color: #8C8C8C;">Ver mapa completo</a>
            </div>
            <div id="mapa-avistamientos"></div>
        </div>

    </div> <!-- Cierre de .container -->

    <?php include 'footer.php'; ?>

    <!-- Scripts para el mapa -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="Geolocalizacion.js"></script>

    <script src="CargaAsync.js"></script>
    <script src="funciones_js.js"></script>
    <script>
        let CargarIncremento = 5;
        let CargarApartirDe = 0;
        const container = document.getElementsByClassName('feed-container')[0];
        const button = document.getElementById("cargar-mas");

        function renderCard(animal) { // Esta función se mantiene aquí porque es específica de esta vista
            return `
            <div class="animal-card" style="position: relative;">
                ${animal.es_refugio == 1 ? '<span class="refugio-tag">REFUGIO</span>' : ''}
                <img src="${animal.imagen}" alt="Foto de ${animal.nombre}">
                <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
                    <h3>${animal.titulo}</h3>
                    <p class="details"><strong>${animal.nombre}</strong> - ${animal.especie} (${animal.raza})</p>
                    <p class="details">
                        ${animal.tamano ? animal.tamano : ''}
                        ${animal.tamano && animal.edad ? ' | ' : ''}
                        ${animal.edad ? animal.edad : ''}
                    </p>
                    <p>${animal.contenido_corto}...</p>`
                    + `<a href="#" class="btn details-btn" onclick='ver_detalles(${JSON.stringify(animal)})'>Ver detalles</a>`
                    +
                    `
                    ${ (sessionData && sessionData.loggedin && sessionData.user && parseInt(sessionData.user.id_usuario) === parseInt(animal.id_publicador))
                        ? '<span class="btn own-post-indicator">Es tu publicación</span>'
                        : (animal.estado === 'Perdido'
                            ? `<a href="reportar_avistamiento.php?id_animal=${animal.id_animal}" class="btn contact-btn report-btn">Reportar Avistamiento</a>`
                            : (sessionData && sessionData.loggedin
                                ? `<a href="enviar_mensaje.php?id_publicacion=${animal.id_publicacion}" class="btn contact-btn">Contactar al Publicador</a>`
                                : `<a href="login.php" class="btn contact-btn">Inicia sesión para contactar</a>`))
                    }
                </div>
            </div>
            `;
        }

        // --- Lógica de Carga y Filtros ---

        // Carga inicial de publicaciones
        document.addEventListener('DOMContentLoaded', function() {
            mostrar_publicaciones('publicaciones', renderCard, container, construirFiltroParaCarga(CargarApartirDe, CargarIncremento));
            CargarApartirDe += CargarIncremento;
        });

        // Botón "Cargar Más"
        button.addEventListener("click", function(){
            mostrar_publicaciones('publicaciones', renderCard, container, construirFiltroParaCarga(CargarApartirDe, CargarIncremento));
            CargarApartirDe += CargarIncremento;
        });

        // Botón "Aplicar Filtros"
        const applyFiltersBtn = document.getElementById('apply-filters');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', function () {
                CargarApartirDe = aplicar_filtros_publicaciones(renderCard, container, button, CargarIncremento);
            });
        }

        // --- Lógica para Limpiar Filtros ---
        const clearFiltersBtn = document.querySelector('.clear-filters-btn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function (e) {
                e.preventDefault(); // Prevenir la navegación del enlace
                limpiar_filtros(); // Llama a la nueva función refactorizada
            });
        }
    </script>
    
    <script>
        // --- Lógica del Mapa ---
        // Llama a la función para la interactividad de los menús
        interactividad_menus();

        // Llama a la nueva función para inicializar el mapa
        mapa_interactivo_index(
            <?php echo $avistamientos_json; ?>,
            <?php echo $perdidos_json; ?>,
            <?php echo $publicaciones_json; ?>
        );

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