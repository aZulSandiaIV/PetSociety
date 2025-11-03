let CargarIncremento = 5;
let CargarApartirDe = 0;
let sessionData = null;

const botton = document.getElementById("cargar-mas");
botton.addEventListener("click", mostrar_publicaciones);

// Cargar info de sesión (si existe)
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

async function cargar_publicaciones() {
    try {
        const response = await fetch('solicitar_publicaciones.php?'
            + 'cargar_apartir=' + CargarApartirDe
            + '&cargar_cantidad=' + CargarIncremento
        );
        
        if (response.status === 204) {
            document.getElementById('cargar-mas').innerHTML = "<p>No hay más publicaciones para cargar.</p>";
            document.getElementById('cargar-mas').disabled = true;
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

function mostrar_publicaciones() {
    const container = document.getElementsByClassName('feed-container')[0];

    cargar_publicaciones().then(data => {
        if (!data || data.length === 0) return;

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

