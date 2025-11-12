
let sessionData = null;

// Exportar en caso de que se necesite en otro módulo
(async () => {
    try {
        const res = await fetch('solicitar/session_check.php', { credentials: 'same-origin' });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const txt = await res.text();
            console.error('session_check.php returned non-JSON:', txt);
        } else {
            sessionData = await res.json();
        }
    } catch (err) {
        console.error('Error fetching session_check.php:', err);
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
function mostrar_publicaciones(directorio, renderCard, container, filtro = '') {

    cargar_datos(directorio, filtro).then(data => {

        console.log('Datos cargados:', data);
        if (!data || data.length === 0){
            if(directorio == 'publicaciones')
                button.innerHTML = 'No hay más publicaciones';
            else if (directorio == 'refugios')
                button.innerHTML = 'No hay más refugios';
            button.disabled = true;
            return false;
        }

        data.forEach(item => {
            // Usar concatenación segura en lugar de asumir que container siempre existe
            container.innerHTML += renderCard(item);
        });

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
 * @param {number} CargarIncremento - La cantidad a cargar.
 */


class CardGestor {
    constructor({ directorio, renderCard, container, button, cargar_cantidad = 5, filtro = '', noMoroDataMenssage = ''}) {
        this.directorio = directorio;
        this.renderCard = renderCard;
        this.container = container;
        this.button = button;

        //filtro
        this.cargar_cantidad = cargar_cantidad;
        this.filtro = filtro;
        cargar_apartir = 0;

        //limpieza
        this.container.innerHTML = '';

        //Setear segun convenga
        this.noMoroDataMenssage = noMoroDataMenssage;
        
        this.load();
    }

    //Pese a que los estan y los acepto, preferiblemente todo seteado desde el inicio
    set filtro(filtroRecibido) {
        this.filtro = filtroRecibido;
    }

    set cargar_cantidad (cantidad){
        this.cargar_cantidad = cantidad;
    }

    set noMoroDataMenssage (message = ''){
        this.noMoroDataMenssage = message;
    }

    load() {
        if (mostrar_publicaciones(this.directorio, this.renderCard, this.container, filtro + `&cargar_desde=${this.cargar_apartir}&cargar_cantidad=${this.cargar_cantidad}`)){
            this.noMoroDataMenssage ?? (this.button.innerHTML = this.noMoroDataMenssage);
            this.button.disabled = true;
        }
        this.cargar_apartir += this.cargar_cantidad;
    }
}
