function ver_detalles(animal) {
    // Llenar el modal con los datos del animal
    document.getElementById('modal-imagen').src = animal.imagen;
    document.getElementById('modal-titulo').textContent = animal.titulo;
    document.getElementById('modal-nombre').textContent = animal.nombre;
    document.getElementById('modal-especie').textContent = animal.especie;
    document.getElementById('modal-raza').textContent = animal.raza || 'No especificada';
    document.getElementById('modal-genero').textContent = animal.genero || 'No especificado';
    document.getElementById('modal-edad').textContent = animal.edad || 'No especificada';
    document.getElementById('modal-tamano').textContent = animal.tamano || 'No especificado';
    document.getElementById('modal-color').textContent = animal.color || 'No especificado';
    document.getElementById('modal-descripcion').innerHTML = animal.descripcion; // Usamos innerHTML por si tiene saltos de línea

    // Mostrar el modal
    document.getElementById('modal-detalles').style.display = 'flex';
}


function cerrarModal() {
    document.getElementById('modal-detalles').style.display = 'none';
}


function interactividad_menus() {
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
}


/**
 * Maneja el cambio en el select de tipo de publicación para adaptar el texto de la etiqueta de características.
 */


function manejarCambioTipoPublicacion() {
    const tipoPublicacionSelect = document.getElementById('tipo_publicacion');
    if (tipoPublicacionSelect) {
        tipoPublicacionSelect.addEventListener('change', function() {
            const labelCaracteristicas = document.getElementById('label_caracteristicas');

            // Solo cambiamos el texto de la etiqueta si es un animal perdido.
            labelCaracteristicas.textContent = (this.value === 'Perdido') 
                ? 'Características distintivas (opcional)' 
                : 'Notas adicionales sobre la ubicación (opcional)';
        });
    }
}


/**
 * Carga el siguiente lote de publicaciones.
 * @param {function} renderCard - La función para renderizar una tarjeta.
 * @param {HTMLElement} container - El contenedor de las publicaciones.
 * @param {number} CargarApartirDe - El índice de inicio.
 * @param {number} CargarIncremento - La cantidad a cargar.
 */


function cargar_mas_publicaciones(renderCard, container, CargarApartirDe, CargarIncremento) {
    mostrar_publicaciones(renderCard, container, construirFiltroParaCarga(CargarApartirDe, CargarIncremento));
}


/**
 * Limpia el contenedor y aplica los filtros seleccionados, reiniciando la paginación.
 * @param {function} renderCard - La función para renderizar una tarjeta.
 * @param {HTMLElement} container - El contenedor de las publicaciones.
 * @param {HTMLElement} button - El botón "Cargar Más".
 * @param {number} CargarIncremento - La cantidad a cargar.
 * @returns {number} El nuevo índice de inicio (siempre 0).
 */


function aplicar_filtros_publicaciones(renderCard, container, button, CargarIncremento) {
    container.innerHTML = ''; // Limpiar contenedor
    
    // Restablecer el botón "Cargar Más"
    if (button) {
        button.innerText = 'Cargar Más';
        button.disabled = false;
    }

    const CargarApartirDe = 0; // Reiniciar paginación
    
    // Mostrar las nuevas publicaciones filtradas
    mostrar_publicaciones(renderCard, container, construirFiltroParaCarga(CargarApartirDe, CargarIncremento));
    
    return CargarApartirDe + CargarIncremento; // Devolvemos el siguiente punto de partida
}


/**
 * Devuelve el ID del usuario logueado desde la variable global sessionData.
 * @returns {number|null} El ID del usuario o null si no está logueado.
 */
function verificar_id_usuario() {
    return (sessionData && sessionData.loggedin && sessionData.user) ? sessionData.user.id_usuario : null;
}


/**
 * Función de ejemplo para verificar la última vista.
 * @returns {boolean} Siempre devuelve true por ahora.
 */
function verificar_ultima_vista() {
    return true;
}


/**
 * Construye una cadena de consulta (query string) con los filtros seleccionados en la interfaz.
 * @returns {string} La cadena de consulta con los filtros.
 */


function getFilterQueryString() {
    const params = new URLSearchParams();

    const search = document.getElementById('search-filter')?.value.trim();
    if (search) params.append('search', search);

    const estado = document.querySelector('input[name="estado"]:checked');
    if (estado) {
        // El backend espera 'status'
        params.append('status', estado.value);
    }

    const especie = document.querySelector('input[name="especie"]:checked');
    if (especie) {
        // El backend espera 'race'
        params.append('race', especie.value);
    }

    const tamano = document.querySelector('input[name="tamano"]:checked');
    if (tamano) {
        // El backend espera 'size'
        params.append('size', tamano.value);
    }

    return params.toString();
}


/**
 * Construye la cadena de filtro completa para la carga asíncrona de publicaciones,
 * incluyendo paginación y filtros de la interfaz.
 * @param {number} CargarApartirDe - El índice desde donde empezar a cargar.
 * @param {number} CargarIncremento - La cantidad de elementos a cargar.
 * @returns {string} La cadena de filtro completa.
 */


function construirFiltroParaCarga(CargarApartirDe, CargarIncremento) {

    const base = `?cargar_apartir=${CargarApartirDe}&cargar_cantidad=${CargarIncremento}`;
    const filtrosQuery = getFilterQueryString();

    const filtro = filtrosQuery ? base + '&' + filtrosQuery : base;

    console.log('Filtro construido:', filtro);
    return filtro;
}


/**
 * Limpia todos los filtros de búsqueda y recarga las publicaciones.
 */


function limpiar_filtros() {
    // Limpiar campo de búsqueda
    const searchFilter = document.getElementById('search-filter');
    if (searchFilter) {
        searchFilter.value = '';
    }

    // Desmarcar todos los radio buttons
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => radio.checked = false);

    // Simular clic en "Aplicar filtros" para recargar las publicaciones sin filtros
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.click();
    }

    // Opcional: hacer scroll a la sección de publicaciones
    const seccionPublicaciones = document.getElementById('seccion-publicaciones');
    if (seccionPublicaciones) {
        seccionPublicaciones.scrollIntoView({ behavior: 'smooth' });
    }
}


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
 * Devuelve el ID del usuario logueado desde la variable global sessionData.
 * @returns {number|null} El ID del usuario o null si no está logueado.
 */
function verificar_id_usuario() {
    return (sessionData && sessionData.loggedin && sessionData.user) ? sessionData.user.id_usuario : null;
}