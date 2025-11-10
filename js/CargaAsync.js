
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
            console.error('publicaciones.php returned non-JSON:', txt);
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
            return null;
        }

        data.forEach(item => {
            // Usar concatenación segura en lugar de asumir que container siempre existe
            container.innerHTML += renderCard(item);
        });

    });

    return true;
}
/* // Clase para manejar la carga de publicaciones
    // No pude implementarlo, pero la idea estuvo aquí
class PublicacionLoader {
    constructor({ directorio, renderCard, container }) {
        this.directorio = directorio;
        this.renderCard = renderCard;
        this.container = container;
    }
    load(filtro = '') {
        return mostrar_publicaciones(this.directorio, this.renderCard, this.container, filtro);
    }
}
*/