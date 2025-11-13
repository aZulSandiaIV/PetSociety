
let sessionData = null;
let sessionReadyResolve;
const sessionReady = new Promise((resolve, reject) => {
    sessionReadyResolve = resolve;
});

// Exportar en caso de que se necesite en otro módulo
(async () => {
    try {
        const res = await fetch('solicitar/session_check.php', { credentials: 'same-origin' });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const txt = await res.text();
            console.error('session_check.php returned non-JSON:', txt);
            // Resolver igualmente para no bloquear consumidores
            sessionReadyResolve(null);
        } else {
            sessionData = await res.json();
            sessionReadyResolve(sessionData);
        }
    } catch (err) {
        console.error('Error fetching session_check.php:', err);
        sessionReadyResolve(null);
    }

})();

async function cargar_datos(directorio ,filtro = '') {
    try {
        const response = await fetch(`solicitar/${directorio}.php${filtro}`);
        
        if (response.status === 204) {
            return null;
        }

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const txt = await response.text();
            console.error(directorio, '.php returned non-JSON:', txt);
            return null;
        }

        const data = await response.json();

        return data;

    } catch (error) {
        console.error('Error al obtener los datos:', error);
        return null;
    }
}

// Ahora recibir una función 'renderCard'
/**
 * 
 * @param {string} directorio 
 * @param {function} renderCard 
 * @param {HTMLElement} container 
 * @param {string} filtro 
 * @returns {boolean}
 */
async function mostrar_publicaciones(directorio, renderCard, container, filtro = '') {

    const data = await cargar_datos(directorio, filtro);

    console.log('Datos cargados:', data);
    if (!data || data.length === 0){
        return false;
    }

    data.forEach(item => {
        // Usar concatenación segura en lugar de asumir que container siempre exista
        container.innerHTML += renderCard(item);
    });

    return true;
}

/**
 * Clase para manejar la carga de cartas (publicaciones, refugios, etc...)
 * Pensado para ser un prearmado funcional, facil de conectar y de usar.
 * Limpia el contenedor y aplica los filtros seleccionados, reiniciando la paginación.
 * @param {string} directorio - Tipo de solicitud de la que se obtendran los datos.
 * @param {function} renderCard - La función para renderizar una tarjeta.
 * @param {HTMLElement} container - El contenedor de las publicaciones.
 * @param {HTMLElement} button - El botón "Cargar Más".
 * @param {number} Cargar_cantidad - La cantidad a cargar.
 * @param {string} noMoreDataMenssage - Cuando no hallan mas cartas remplazar con esto. 
 */


class CardGestor {

    constructor(directorio, renderCard, container, button, cargar_cantidad = 5, filtro = '', noMoreDataMenssage = '') {
        this.directorio = directorio;
        this.renderCard = renderCard;
        this.container = container;
        this.button = button;

        //filtro
        this.cargar_cantidad = cargar_cantidad;
        this.filtro = filtro;
        this.cargar_apartir = 0;

        //limpieza
        this.container.innerHTML = '';

        this.noMoreDataMenssage = noMoreDataMenssage;
        
        button.addEventListener("click", () => this.load());

        sessionReady.then(() => this.load());
    }

    async load() {

        const base = `?cargar_apartir=${this.cargar_apartir}&cargar_cantidad=${this.cargar_cantidad}`;
        const filtroQuery = this.filtro ? base + '&' + this.filtro : base;
        const hasMore = await mostrar_publicaciones(this.directorio, this.renderCard, this.container, filtroQuery);

        if (hasMore) {
            this.cargar_apartir += this.cargar_cantidad;
        } else {

            this.button.disabled = true;

            this.button.textContent = this.noMoreDataMenssage;
  
        }

        return hasMore;
    }

}
