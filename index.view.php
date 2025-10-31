<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a PetSociety</title>
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
           color: red;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden; /* Para que la imagen no se salga de los bordes redondeados */
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease-in-out;
            font-family: 'Roboto', sans-serif;
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
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 1px;
            font-weight: bold;
        }
        .animal-card p {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .animal-card .details {
            color: #666;
            font-size: 0.9rem;
        }
        .animal-card .contact-btn {
            margin-top: auto; /* Empuja el botón al final de la tarjeta */
            background-color: #97BC62;
            text-align: center;
        }
        .animal-card .own-post-indicator {
            margin-top: auto;
            background-color: #ccc;
            cursor: default;
        }
        .refugio-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #2C5F2D;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75em;
        }
        /* Estilo para el mapa */
        #mapa-avistamientos {
            height: 500px; width: 100%; margin-bottom: 30px; border-radius: 8px;
        }

        /*------banner--------*/
        .banner {
    
    
                background-size: contain;
                background-position: center;
                background-repeat: no-repeat;    
                color: white;
                background-color: #f2f3ee; 
                padding-top: 100px;
                text-align: center;
                min-height:650px;  /* Cambia de 500px a 600px o más */
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                list-style: none;
                
    ...
        }
          body 
       {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 16px;
        }
         h1, h2, h3, h4, h5, h6
         {
             font-family: 'Montserrat', sans-serif;
         }

        button, .btn, a.button , ul,li
        {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <ul>
                    <li>Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/index.php" style="color: #b91414ff;">Panel Admin</a></li>
                    <?php endif; ?>
                    <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
                    <li><a href="buzon.php">Buzón</a></li>
                    <li><a href="publicar.php">Publicar Animal</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

<div class="banner" style="background-image: url('img/download.png');">
        <h1>
            ¿Perdiste o Encontraste?
            <br>
            ¿Quieres dar en adopcion?
        </h1>
        <li><a href="publicar.php" class="banner-button">Publicar ahora</a></li>
</div>

    <div class="container">
        <!-- SECCIÓN DEL MAPA DE AVISTAMIENTOS -->
        <div style="margin-bottom: 30px;">
            <h2>Mapa de Avistamientos Recientes</h2>
            <p>Estos son los últimos avistamientos reportados. Haz clic en un marcador para ver los detalles o usa el botón para ver tu posición.</p>
            <button id="ver-mi-ubicacion" class="btn" style="margin-bottom: 15px; width: auto;">Mostrar mi ubicación</button>
            <div id="mapa-avistamientos"></div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Publicaciones Recientes</h2>
            <form action="index.php" method="get" style="display: flex; align-items: center; gap: 10px;">
                <label for="filtro">Filtrar por:</label>
                <select name="filtro" id="filtro" onchange="this.form.submit()" class="form-group" style="margin-bottom: 0; padding: 8px;">
                    <option value="Todos" <?php if ($filtro_estado == 'Todos') echo 'selected'; ?>>Todos</option>
                    <option value="En Adopción" <?php if ($filtro_estado == 'En Adopción') echo 'selected'; ?>>En Adopción</option>
                    <option value="Hogar Temporal" <?php if ($filtro_estado == 'Hogar Temporal') echo 'selected'; ?>>Hogar Temporal</option>
                    <option value="Perdido" <?php if ($filtro_estado == 'Perdido') echo 'selected'; ?>>Perdido</option>
                    <option value="Refugio" <?php if ($filtro_estado == 'Refugio') echo 'selected'; ?>>Solo Refugios</option>
                </select>
            </form>
        </div>

        <div class="feed-container">
            <!-- Aquí se cargarán las publicaciones vía JavaScript -->
        </div>
        <div style="text-align: center; margin: 20px 0;"> <!-- Esperando un terminado mejor, usar ID cargar-mas -->
            <button id="cargar-mas" class="btn">Cargar más publicaciones</button>
        </div>

    </div>

    <!-- Scripts para el mapa -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="geolocalizacion.js"></script>

    <script src="js/Post_CargaAsync.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', mostrar_publicaciones);
    </script>
    
    <script>
        // --- Lógica del Mapa ---
        // La variable 'mapa' ahora es global (declarada en geolocalizacion.js)
        mapa = L.map('mapa-avistamientos').setView([-34.60, -58.38], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);

        // --- Marcadores de Avistamientos (huella) ---
        const huellaIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/12/12195.png',
            iconSize:     [32, 32],
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
        const alertaIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/753/753345.png',
            iconSize:     [32, 32],
            iconAnchor:   [16, 32],
            popupAnchor:  [0, -32]
        });
        const perdidos = <?php echo $perdidos_json; ?>;
        perdidos.forEach(perdido => {
            L.marker([perdido.latitud, perdido.longitud], {icon: alertaIcon})
                .addTo(mapa)
                .bindPopup(perdido.popup_html);
        });

        // --- Lógica para mostrar la ubicación del usuario ---
        document.getElementById('ver-mi-ubicacion').addEventListener('click', function() {
            this.textContent = 'Buscando...';
            this.disabled = true;

            // Sobrescribimos la función EXITO para que llame a PONER_EN_MAPA
            EXITO = function(position) {
                PONER_EN_MAPA(position.coords.latitude, position.coords.longitude);
            };

            OBTENER_POSICION_ACTUAL();
        });
    </script>

</body>
</html>