// --- Variables Globales ---
var mapa; // Para guardar la instancia del mapa de Leaflet
var marcadorUsuario; // Para guardar el marcador del usuario y reutilizarlo
var RANGO_KM; // Para guardar el valor del filtro de rango en km

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
 * Comprueba si un punto geográfico está dentro de un rango específico desde un punto de referencia.
 * El punto de referencia puede ser la ubicación del usuario o el centro del mapa.
 * @param {number} latitud - Latitud del punto a comprobar.
 * @param {number} longitud - Longitud del punto a comprobar.
 * @param {number} rangoKm - El radio de distancia en kilómetros para la comprobación.
 * @returns {boolean} - Devuelve true si el punto está dentro del rango, de lo contrario false.
 */
function buscar_por_zona(latitud, longitud, rangoKm) {
    let refLat = null, refLng = null;

    // Prioridad 1: Usar la posición del usuario si está disponible.
    if (window.pos && window.pos.coords && typeof window.pos.coords.latitude === 'number' && typeof window.pos.coords.longitude === 'number') {
        refLat = window.pos.coords.latitude;
        refLng = window.pos.coords.longitude;
    // Prioridad 2: Usar el centro del mapa.
    } else if (typeof mapa !== 'undefined' && mapa && typeof mapa.getCenter === 'function') {
        const center = mapa.getCenter();
        refLat = (typeof center.lat === 'function') ? center.lat() : center.lat;
        refLng = (typeof center.lng === 'function') ? center.lng() : center.lng;
    } else {
        // Si no hay punto de referencia, no se puede filtrar, así que se muestra todo.
        return true;
    }

    // Función interna para calcular la distancia usando la fórmula de Haversine.
    function haversineKm(lat1, lon1, lat2, lon2) {
        const toRad = v => v * Math.PI / 180;
        const R = 6371; // Radio de la Tierra en km
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    const latA = parseFloat(refLat), lngA = parseFloat(refLng);
    const latB = parseFloat(latitud), lngB = parseFloat(longitud);

    if ([latA, lngA, latB, lngB].some(v => Number.isNaN(v))) return true; // Si hay coordenadas inválidas, no filtrar.

    return haversineKm(latA, lngA, latB, lngB) <= rangoKm;
}


/**
 * Inicializa el filtro de rango, vinculando el control deslizante (slider)
 * con las funciones que renderizan los marcadores en el mapa.
 * @param {string} inputId - El ID del input tipo 'range'.
 * @param {string} displayId - El ID del elemento que muestra el valor del rango.
 */
function inicializar_filtro_rango(inputId, displayId) {
    const rangoInput = document.getElementById(inputId);
    const rangoValor = document.getElementById(displayId);

    if (!rangoInput || !rangoValor) {
        console.warn("No se encontraron los elementos para el filtro de rango.");
        return;
    }

    // Inicializa el valor del rango
    RANGO_KM = parseFloat(rangoInput.value) || 1;

    rangoInput.addEventListener('input', function () {
        RANGO_KM = parseFloat(this.value);
        rangoValor.textContent = RANGO_KM + ' km';
        renderizar_publicaciones_mapa('avistamientos');
        renderizar_publicaciones_mapa('perdidos');
        renderizar_publicaciones_mapa('publicaciones');
    });
}

/**
 * Dibuja los marcadores en una capa específica del mapa, aplicando filtros de zona.
 * @param {L.LayerGroup} layer - La capa de Leaflet donde se dibujarán los marcadores.
 * @param {Array} data - El array de datos para los marcadores.
 * @param {string} tipo - El tipo de marcador ('avistamientos', 'perdidos', 'publicaciones').
 * @param {L.Icon} [icon] - El icono por defecto para los marcadores (no aplica para 'publicaciones').
 */
function dibujar_marcadores(layer, data, tipo, icon) {
    layer.clearLayers();
    data.forEach(item => {
        let markerIcon = icon;
        if (tipo === 'publicaciones') {
            markerIcon = item.es_refugio ? window.refugioIcon : window.adopcionIcon;
        }

        if (item.latitud && item.longitud && buscar_por_zona(item.latitud, item.longitud, RANGO_KM)) {
            const marker = L.marker([item.latitud, item.longitud], { icon: markerIcon });
            if (item.popup_html) {
                marker.bindPopup(item.popup_html);
            }
            marker.addTo(layer);
        }
    });
}

/**
 * Renderiza los marcadores en el mapa según el tipo especificado.
 * @param {string} tipo - El tipo de marcador a renderizar ('avistamientos', 'perdidos', 'publicaciones').
 */
function renderizar_publicaciones_mapa(tipo) {
    let layer, icon, data;

    switch (tipo) {
        case 'avistamientos':
            layer = window.avistamientosLayer;
            icon = window.huellaIcon;
            data = window.avistamientos;
            break;
        case 'perdidos':
            layer = window.perdidosLayer;
            icon = window.alertaIcon;
            data = window.perdidos;
            break;
        case 'publicaciones':
            layer = window.publicacionesLayer;
            data = window.publicaciones;
            break;
        default:
            console.error("Tipo de marcador no válido:", tipo);
            return;
    }

    dibujar_marcadores(layer, data, tipo, icon);
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
 * Activa un botón para obtener la ubicación del usuario usando la API de Leaflet,
 * mostrando la posición en el mapa y actualizando los filtros.
 * @param {string} buttonId - El ID del botón que activa la geolocalización.
 */


function mostrar_ubicacion_usuario(buttonId) {
    const boton = document.getElementById(buttonId);
    if (!boton || typeof mapa === 'undefined') {
        console.warn("El botón de ubicación o el mapa no están disponibles.");
        return;
    }

    boton.addEventListener('click', function() {
        this.textContent = 'Buscando...';
        this.disabled = true;

        mapa.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true, timeout: 10000 });

        mapa.on('locationfound', function(e) {
            // Guardar la posición para que la use `buscar_por_zona`
            window.pos = { coords: { latitude: e.latlng.lat, longitude: e.latlng.lng } };

            if (marcadorUsuario) {
                mapa.removeLayer(marcadorUsuario);
            }
            marcadorUsuario = L.marker(e.latlng).addTo(mapa)
                .bindPopup(`<b>¡Estás aquí!</b><br>Precisión: ${Math.round(e.accuracy)} metros.`)
                .openPopup();

            boton.textContent = 'Buscar por mi zona';
            boton.disabled = false;

            // Re-renderizar los marcadores con la nueva ubicación de referencia
            renderizar_publicaciones_mapa('avistamientos');
            renderizar_publicaciones_mapa('perdidos');
            renderizar_publicaciones_mapa('publicaciones');
        });

        mapa.on('locationerror', function(e) {
            alert("Error al obtener la ubicación: " . e.message);
            boton.textContent = 'Buscar por mi zona';
            boton.disabled = false;
            console.error("Error de geolocalización:", e.message);
        });
    });
}


/**
 * Crea los marcadores y capas para los avistamientos, perdidos y publicaciones.
 * @param {Array} avistamientos - Array con los datos de los avistamientos.
 * @param {Array} perdidos - Array con los datos de los perdidos.
 * @param {Array} publicaciones - Array con los datos de las publicaciones.
 */
function creacion_marcadores_mapa(avistamientos, perdidos, publicaciones) {
    // --- Iconos personalizados ---
    window.huellaIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/12/12195.png',
        iconSize: [28, 28],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    window.alertaIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/753/753345.png',
        iconSize: [28, 28],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    window.adopcionIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/1077/1077035.png',
        iconSize: [28, 28],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    window.refugioIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/619/619153.png',
        iconSize: [28, 28],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    // --- Capas para los marcadores ---
    window.avistamientosLayer = L.layerGroup().addTo(mapa);
    window.perdidosLayer = L.layerGroup().addTo(mapa);
    window.publicacionesLayer = L.layerGroup().addTo(mapa);

    // --- Guardar los datos en variables globales ---
    window.avistamientos = avistamientos;
    window.perdidos = perdidos;
    window.publicaciones = publicaciones;
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


function mapa_interactivo_index(avistamientos, perdidos, publicaciones) 
{
    // La variable 'mapa' ahora es global (declarada al inicio del archivo)
    mapa = L.map('mapa-avistamientos').setView([-34.60, -58.38], 12); // Zoom inicial más apropiado
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapa);
    
    // Creacion de Marcadores
    creacion_marcadores_mapa(avistamientos, perdidos, publicaciones);
 
    // Renderizacion de marcadores
    renderizar_publicaciones_mapa('avistamientos');
    renderizar_publicaciones_mapa('perdidos');
    renderizar_publicaciones_mapa('publicaciones');

    // Funciones
    inicializar_filtro_rango('rango-km', 'rango-valor');
    mostrar_ubicacion_usuario('ver-mi-ubicacion');
}

/**
 * Orquesta la creación y población del mapa de avistamientos en la página principal.
 * @param {Array} avistamientos - Array de datos de avistamientos.
 * @param {Array} perdidos - Array de datos de animales perdidos.
 * @param {Array} publicaciones - Array de datos de publicaciones.
 */

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
            ERROR = function(error) {
                alert("Error al obtener la ubicación: " + error.message);
                if (mensajeDiv) mensajeDiv.textContent = 'Error al obtener ubicación.';
                botonObtener.disabled = false;
            };
            OBTENER_POSICION_ACTUAL();
        });
    }
}
