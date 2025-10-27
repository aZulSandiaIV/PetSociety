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
