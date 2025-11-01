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
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                        <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                                    <?php endif; ?>
                                    <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
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
                <a href="publicar.php?estado=Encontrado" class="hero-btn encontraste">Encontré una Mascota perdida</a>
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
                <form action="index.php#seccion-publicaciones" method="get" id="filter-form">
                    <div class="filters-header">
                        <h3>Filtros</h3>
                        <a href="index.php#seccion-publicaciones" class="clear-filters-btn">Limpiar</a>
                    </div>

                    <div class="filter-group">
                        <input type="text" name="q" class="form-group" placeholder="Buscar por palabra clave..." value="<?php echo htmlspecialchars($filtros['q']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="filter-group">
                        <h4>Estado</h4>
                        <?php $estados = ['En Adopción', 'Hogar Temporal', 'Perdido']; ?>
                        <?php foreach ($estados as $estado): ?>
                            <label>
                                <input type="checkbox" name="estado[]" value="<?php echo $estado; ?>" <?php if (in_array($estado, $filtros['estado'])) echo 'checked'; ?>>
                                <span><?php echo $estado; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Especie</h4>
                        <?php $especies = ['Perro', 'Gato', 'Otro']; ?>
                        <?php foreach ($especies as $especie): ?>
                            <label>
                                <input type="checkbox" name="especie[]" value="<?php echo $especie; ?>" <?php if (in_array($especie, $filtros['especie'])) echo 'checked'; ?>>
                                <span><?php echo $especie; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Tamaño</h4>
                        <?php $tamaños = ['Pequeño', 'Mediano', 'Grande']; ?>
                        <?php foreach ($tamaños as $tamaño): ?>
                            <label>
                                <input type="checkbox" name="tamaño[]" value="<?php echo $tamaño; ?>" <?php if (in_array($tamaño, $filtros['tamaño'])) echo 'checked'; ?>>
                                <span><?php echo $tamaño; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">Aplicar Filtros</button>
                </form>
            </aside>
            <!-- FIN DE LA BARRA LATERAL DE FILTROS -->

            <!-- INICIO DEL CONTENIDO PRINCIPAL (FEED) -->
            <main class="content-main">
                <div class="feed-container">
                    <?php if (!empty($animales)): ?>
                        <?php foreach ($animales as $animal): ?>
                            <div class="animal-card" style="position: relative;">
                                <?php if ($animal['es_refugio']): ?>
                                    <span class="refugio-tag">REFUGIO</span>
                                <?php endif; ?>
                                <img src="<?php echo $animal['imagen']; ?>" alt="Foto de <?php echo $animal['nombre']; ?>">
                                <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
                                    <h3><?php echo $animal['titulo']; ?></h3>
                                    <p class="details"><strong><?php echo $animal['nombre']; ?></strong> - <?php echo $animal['especie']; ?> (<?php echo $animal['raza']; ?>)</p>
                                    <p class="details">
                                        <?php echo $animal['tamaño']; ?>
                                        <?php if($animal['tamaño'] && $animal['edad']) echo ' | '; ?>
                                        <?php echo $animal['edad']; ?>
                                    </p>
                                    <p><?php echo $animal['contenido_corto']; ?>...</p>
                                    <?php if (isset($_SESSION['id_usuario']) && $animal['id_publicador'] == $_SESSION['id_usuario']): ?>
                                        <span class="btn own-post-indicator">Es tu publicación</span>
                                    <?php else: ?>
                                        <?php if ($animal['estado'] == 'Perdido'): ?>
                                            <a href="reportar_avistamiento.php?id_animal=<?php echo $animal['id_animal']; ?>" class="btn contact-btn report-btn">Reportar Avistamiento</a>
                                        <?php else: ?>
                                            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                                <a href="enviar_mensaje.php?id_publicacion=<?php echo $animal['id_publicacion']; ?>" class="btn contact-btn">Contactar al Publicador</a>
                                            <?php else: ?>
                                                <a href="login.php" class="btn contact-btn">Inicia sesión para contactar</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; grid-column: 1 / -1; padding: 40px; background: #fff; border-radius: 8px;">
                            <h3>No se encontraron resultados</h3>
                            <p>Prueba a cambiar o limpiar los filtros para ver más publicaciones.</p>
                            <a href="index.php#seccion-publicaciones" class="btn">Limpiar Filtros</a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
             <!-- FIN DEL CONTENIDO PRINCIPAL (FEED) -->
        </div>

        <!-- SECCIÓN DEL MAPA DE AVISTAMIENTOS -->
        <div style="margin-bottom: 30px; margin-top: 40px;">
            <h2>Has visto a un animal perdido?</h2>
            <p>Explora el mapa para ver los últimos avistamientos y animales perdidos en tu zona. Cada marcador te muestra información importante para ayudar a reunirlos con sus familias.</p>
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <button id="ver-mi-ubicacion" class="btn" style="width: auto;">Mostrar mi ubicación</button>
                <a href="reportar_avistamiento_mapa.php" class="btn" style="width: auto; background-color: #7A9BA8;">Reportar Callejero</a>
            </div>
            <div id="mapa-avistamientos"></div>
        </div>
    </div>

    <!-- Scripts para el mapa -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="geolocalizacion.js"></script>
    <script>
        // --- Lógica del Mapa ---
        // La variable 'mapa' ahora es global (declarada en geolocalizacion.js)
        mapa = L.map('mapa-avistamientos').setView([-34.60, -58.38], 12); // Zoom inicial más apropiado

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);

        // --- Marcadores de Avistamientos (huella) ---
        // Icono personalizado para avistamientos
        const huellaIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/12/12195.png',
            iconSize:     [28, 28],
            iconAnchor:   [16, 32],
            popupAnchor:  [0, -32]
        });
        const avistamientos = <?php echo $avistamientos_json; ?>;
        avistamientos.forEach(avistamiento => {
            L.marker([avistamiento.latitud, avistamiento.longitud], {icon: huellaIcon})
                .addTo(mapa)
                .bindPopup(avistamiento.popup_html);
        });

        // --- Marcadores de Animales Perdidos (alerta) ---
        // Icono personalizado para animales perdidos
        const alertaIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/753/753345.png',
            iconSize:     [28, 28],
            iconAnchor:   [16, 32],
            popupAnchor:  [0, -32]
        });
        const perdidos = <?php echo $perdidos_json; ?>;
        perdidos.forEach(perdido => {
            L.marker([perdido.latitud, perdido.longitud], {icon: alertaIcon})
                .addTo(mapa)
                .bindPopup(perdido.popup_html);
        });

        // --- Marcadores de Publicaciones (Adopción, Hogar Temporal, Refugios) ---
        const adopcionIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/1077/1077035.png', // Icono de corazón
            iconSize:     [28, 28],
            iconAnchor:   [16, 32],
            popupAnchor:  [0, -32]
        });
        const refugioIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/619/619153.png', // Icono de casa
            iconSize:     [28, 28],
            iconAnchor:   [16, 32],
            popupAnchor:  [0, -32]
        });

        const publicaciones = <?php echo $publicaciones_json; ?>;
        publicaciones.forEach(pub => {
            // Elegir el icono: si es refugio, el de refugio, si no, el de adopción
            const icon = pub.es_refugio ? refugioIcon : adopcionIcon;

            L.marker([pub.latitud, pub.longitud], {icon: icon})
                .addTo(mapa)
                .bindPopup(pub.popup_html);
        });


        // --- Lógica para mostrar la ubicación del usuario ---
        document.getElementById('ver-mi-ubicacion').addEventListener('click', function() {
            this.textContent = 'Buscando...';
            this.disabled = true;

            // Sobrescribimos la función EXITO para que llame a la función correcta 'ponerEnMapa'
            // que está definida en geolocalizacion.js
            EXITO = function(position) {
                PONER_EN_MAPA(position.coords.latitude, position.coords.longitude);
                document.getElementById('ver-mi-ubicacion').textContent = 'Mostrar mi ubicación';
            };

            OBTENER_POSICION_ACTUAL();
        });

        // Si hay un hash en la URL, hacer scroll a esa sección
        window.addEventListener('load', function() {
            if (window.location.hash === '#seccion-publicaciones') {
                document.getElementById('seccion-publicaciones').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const mobileUserMenu = document.querySelector('.mobile-user-menu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });

                document.addEventListener('click', function(event) {
                    if (!navMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    }
                });

                const navLinks = navMenu.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    });
                });
            }

            if (mobileUserMenu) {
                const userMenuTrigger = mobileUserMenu.querySelector('.user-menu-trigger');
                if (userMenuTrigger) {
                    userMenuTrigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        mobileUserMenu.classList.toggle('active');
                    });
                }
            }
        });
    </script>

</body>
</html>