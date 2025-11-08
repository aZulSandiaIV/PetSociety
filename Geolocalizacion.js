// --- Variables Globales ---
var mapa; // Para guardar la instancia del mapa de Leaflet
var marcadorUsuario; // Para guardar el marcador del usuario y reutilizarlo

const OPCIONES = {
    enableHighAccuracy: true,
    timeout: 5000,
    maximumAge: 0
};

// --- Funciones ---

function EXITO(position) {
    console.log("Ubicación obtenida con éxito.");
    // Esta función se sobreescribirá en cada página según sea necesario.
}

function ERROR(error) {
    console.error("Error al obtener ubicación:", error.message);
    alert("Error al obtener la ubicación: " + error.message);
}

function OBTENER_POSICION_ACTUAL() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(EXITO, ERROR, OPCIONES);
    } else {
        alert("Tu navegador no soporta geolocalización.");
    }
}

function PONER_EN_MAPA(latitud, longitud) {
    const posicion = [latitud, longitud];

    // Si el mapa no existe, lo crea. Esto es para ver_mapa.html
    if (!mapa) {
        console.log("Creando un nuevo mapa...");
        // Busca el div por el ID que corresponda
        const mapDivId = document.getElementById('mapa-avistamientos') ? 'mapa-avistamientos' : 'mapa';
        mapa = L.map(mapDivId).setView(posicion, 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);
    }

    // Gestiona el marcador del usuario
    if (!marcadorUsuario) {
        console.log("Creando marcador de usuario...");
        marcadorUsuario = L.marker(posicion).addTo(mapa)
            .bindPopup('<b>¡Estás aquí!</b>')
            .openPopup();
    } else {
        console.log("Actualizando marcador de usuario...");
        marcadorUsuario.setLatLng(posicion).openPopup();
    }

    // Centra el mapa en la nueva ubicación
    mapa.setView(posicion, 15);
}

/**
 * Inicializa un mapa de Leaflet para seleccionar una ubicación.
 * @param {string} idMapa - El ID del div contenedor del mapa.
 * @param {string} idInputLat - El ID del input para la latitud.
 * @param {string} idInputLon - El ID del input para la longitud.
 * @param {string} idInputUbicacion - El ID del input para el texto de la ubicación.
 */
function inicializarMapaDeSeleccion(idMapa, idInputLat, idInputLon, idInputUbicacion) {
    const latitudInput = document.getElementById(idInputLat);
    const longitudInput = document.getElementById(idInputLon);
    const ubicacionTextoInput = document.getElementById(idInputUbicacion);

    // 1. Inicializar el mapa
    const mapaSeleccion = L.map(idMapa).setView([-34.60, -58.38], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapaSeleccion);

    // 2. Crear un marcador arrastrable
    let marcador = L.marker(mapaSeleccion.getCenter(), { draggable: true }).addTo(mapaSeleccion);
    marcador.bindPopup("Arrastra este marcador a la ubicación.").openPopup();

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
    return { mapa: mapaSeleccion, marcador: marcador, actualizarCampos: actualizarCampos };
}

/**
 * Configura el botón "Usar mi ubicación actual" para obtener y mostrar la ubicación del usuario en el mapa.
 * @param {object} mapaInfo - Objeto que contiene la instancia del mapa, el marcador y la función actualizarCampos.
 */
function usar_ubicacion_actual(mapaInfo) {
    const botonUbicacion = document.getElementById('usar-ubicacion-actual');
    if (botonUbicacion) {
        botonUbicacion.addEventListener('click', function() {
            this.textContent = 'Obteniendo...';
            this.disabled = true;

            // Sobrescribimos la función EXITO de geolocalizacion.js
            EXITO = function(position) {
                const { latitude, longitude } = position.coords;
                mapaInfo.mapa.setView([latitude, longitude], 16);
                mapaInfo.marcador.setLatLng([latitude, longitude]);
                botonUbicacion.textContent = '¡Ubicación Obtenida!';
                mapaInfo.actualizarCampos(latitude, longitude); // Usar la función de la instancia del mapa
            };
            OBTENER_POSICION_ACTUAL();
        });
    }
}


/**
 * Configura un botón para obtener la ubicación del usuario y mostrarla en el mapa.
 * @param {string} buttonId - El ID del botón que activa la geolocalización.
 */

function mostrar_ubicacion_usuario(buttonId) {
    const boton = document.getElementById(buttonId);
    if (!boton) return; // Si el botón no existe en la página, no hacer nada.

    boton.addEventListener('click', function() {
        this.textContent = 'Buscando...';
        this.disabled = true;
        
        EXITO = function(position) {
            PONER_EN_MAPA(position.coords.latitude, position.coords.longitude);
            boton.textContent = 'Mostrar mi ubicación';
            boton.disabled = false; // Reactivar el botón
        };
        OBTENER_POSICION_ACTUAL();
    });
}


/**
 * Inicializa un mapa y muestra los marcadores de avistamientos.
 * @param {string} mapId - El ID del div contenedor del mapa.
 * @param {Array} avistamientosData - Un array de objetos de avistamiento.
 */


/**
 * Inicializa el mapa interactivo en la página principal y añade marcadores.
 * @param {string} avistamientosJsonString - JSON string de avistamientos.
 * @param {string} perdidosJsonString - JSON string de animales perdidos.
 * @param {string} publicacionesJsonString - JSON string de publicaciones (adopción/hogar temporal).
 */


function mapa_interactivo_index(avistamientos, perdidos, publicaciones) {
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

    publicaciones.forEach(pub => {
        // Elegir el icono: si es refugio, el de refugio, si no, el de adopción
        const icon = pub.es_refugio ? refugioIcon : adopcionIcon;

        L.marker([pub.latitud, pub.longitud], {icon: icon})
            .addTo(mapa)
            .bindPopup(pub.popup_html);
    });

    // Llama a la función refactorizada para el botón de ubicación
    mostrar_ubicacion_usuario('ver-mi-ubicacion');
}

/**
 * Configura un botón para obtener la ubicación del usuario y rellenar campos de un formulario.
 * @param {string} buttonId - El ID del botón que inicia la geolocalización.
 * @param {string} latInputId - El ID del input para la latitud.
 * @param {string} lonInputId - El ID del input para la longitud.
 * @param {string} submitButtonId - El ID del botón de envío que se habilitará.
 * @param {string} messageDivId - El ID del div donde se mostrarán los mensajes de estado.
 */


function obtener_ubicacion_para_formulario(buttonId, latInputId, lonInputId, submitButtonId, messageDivId) {
    const botonObtener = document.getElementById(buttonId);
    const mensajeDiv = document.getElementById(messageDivId);

    if (botonObtener) {
        botonObtener.addEventListener('click', function() {
            if (mensajeDiv) mensajeDiv.textContent = 'Buscando tu ubicación...';
            this.disabled = true;

            EXITO = function(position) {
                document.getElementById(latInputId).value = position.coords.latitude;
                document.getElementById(lonInputId).value = position.coords.longitude;
                if (mensajeDiv) mensajeDiv.textContent = '¡Ubicación obtenida con éxito! Ya puedes enviar el reporte.';
                document.getElementById(submitButtonId).disabled = false;
            };
            OBTENER_POSICION_ACTUAL();
        });
    }
}
