let CargarIncremento = 5;
let CargarApartirDe = 0;
let filtro = '';
let sessionData = null;

const botton = document.getElementById("cargar-mas"); //id para botones de cargar más
botton.addEventListener("click", mostrar_publicaciones_index);

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
/*
document.getElementById('filter-form').addEventListener('submit', function(event) {
    event.preventDefault();
    var formData = new FormData(this);
    // Puedes usar un bucle o el método getAll() para obtener los valores
    var email = formData.getAll('estado').length; // O formData.getAll('campo') para varios valores
    // Realiza la acción deseada, como enviar con AJAX
    console.log(email);
});
*/

function modificar_filtros() {

    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    /*
    for (const [key, value] of formData.entries()) {
        if (params.has(key)) {
            params.append(key, value);
        } else {
            params.set(key, value);
        }
    }
    */

    console.log(formData.especie);

    filtro = '&' + params.toString();
    CargarApartirDe = 0;
    document.getElementsByClassName('feed-container')[0].innerHTML = '';
    
    console.log('Aplicando filtros:', filtro);
    mostrar_publicaciones_index(filtro);
}

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

function mostrar_publicaciones_index() {
    const container = document.getElementsByClassName('feed-container')[0];

    cargar_publicaciones(`
                        ?cargar_apartir=${CargarApartirDe}&cargar_cantidad=${CargarIncremento}
                        ${filtro}
                        `).then(data => {

        if (!data || data.length === 0){
            document.getElementById('cargar-mas').innerHTML = "<p>No hay más publicaciones para cargar.</p>";
            document.getElementById('cargar-mas').disabled = true;
            return;
        }

        data.forEach(animal => {
            
            container.innerHTML += `
                <div class="animal-card" style="position: relative;">
                    ${animal.es_refugio == 1 ? '<span class="refugio-tag">REFUGIO</span>' : ''}
                    <img src="${animal.imagen}" alt="Foto de ${animal.nombre}">
                    <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
                        <h3>${animal.titulo}</h3>
                        <p class="details"><strong>${animal.nombre}</strong> - ${animal.especie} (${animal.raza})</p>
                        <p class="details">
                            ${animal.tamano ? animal.tamano : ''}
                            ${animal.tamano && animal.edad ? ' | ' : ''}
                            ${animal.edad ? animal.edad : ''}
                        </p>
                        <p>${animal.contenido_corto}...</p>
                        ${ (sessionData && sessionData.loggedin && sessionData.user && sessionData.user.id_usuario === animal.id_publicador)
                            ? '<span class="btn own-post-indicator">Es tu publicación</span>'
                            : (animal.estado === 'Perdido'
                                ? `<a href="reportar_avistamiento.php?id_animal=${animal.id_animal}" class="btn contact-btn report-btn">Reportar Avistamiento</a>`
                                : (sessionData && sessionData.loggedin
                                    ? `<a href="enviar_mensaje.php?id_publicacion=${animal.id_publicacion}" class="btn contact-btn">Contactar al Publicador</a>`
                                    : `<a href="login.php" class="btn contact-btn">Inicia sesión para contactar</a>`))
                        }
                    </div>
                </div>
            `;
        });

        CargarApartirDe += CargarIncremento;
    });
}

