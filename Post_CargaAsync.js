
let sessionData = null;

// Exportar en caso de que se necesite en otro módulo
(async () => {
    try {
        const res = await fetch('session_check.php', { credentials: 'same-origin' });
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

async function cargar_publicaciones(filtro = '') {
    try {
        const response = await fetch('solicitar_publicaciones.php' + filtro);
        
        if (response.status === 204) {
            return null;
        }

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const txt = await response.text();
            console.error('solicitar_publicaciones.php returned non-JSON:', txt);
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
function mostrar_publicaciones(renderCard, container, filtro = '') {

    cargar_publicaciones(filtro).then(data => {

        if (!data || data.length === 0){
            button.innerHTML = 'No hay más publicaciones';
            button.disabled = true;
            return null;
        }

        data.forEach(animal => {
            container.innerHTML += renderCard(animal);
        });

    });

    return true;
}

