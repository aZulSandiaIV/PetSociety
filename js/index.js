const container = document.getElementsByClassName('feed-container')[0];
const button = document.getElementById("cargar-mas");
let feed;

function renderCard(animal) { // Esta función se mantiene aquí porque es específica de esta vista
    return `
    <div class="animal-card" style="position: relative;">
        ${animal.es_refugio == 1 ? '<span class="refugio-tag">REFUGIO</span>' : ''}
        <img src="${animal.imagen}" alt="Foto de ${animal.nombre}">
        <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
            <div>
                <h3>${animal.titulo}</h3>
                <p class="details"><strong>${animal.nombre}</strong> - ${animal.especie} (${animal.raza})</p>
                <p class="details">
                    ${animal.tamano ? animal.tamano : ''}
                    ${animal.tamano && animal.edad ? ' | ' : ''}
                    ${animal.edad ? animal.edad : ''}
                </p>
                <p>${animal.contenido_corto}...</p>
            </div>
            <div class="btn-container">`
            + `<a href="#" class="btn details-btn" onclick='ver_detalles(${JSON.stringify(animal)})'>Ver detalles</a>`
            +
            `
            
                ${ (sessionData && sessionData.loggedin && sessionData.user && parseInt(sessionData.user.id_usuario) === parseInt(animal.id_publicador))
                    ? '<span class="btn own-post-indicator">Es tu publicación</span>'
                    : (animal.estado === 'Perdido'
                        ? `<a href="reportar_avistamiento.php?id_animal=${animal.id_animal}" class="btn contact-btn report-btn">Reportar Avistamiento</a>`
                        : (sessionData && sessionData.loggedin
                            ? `<a href="enviar_mensaje.php?id_publicacion=${animal.id_publicacion}" class="btn contact-btn">Contactar al Publicador</a>`
                            : `<a href="login.php" class="btn contact-btn">Inicia sesión para contactar</a>`))
                }
            </div>
        </div>
    </div>
    `;
}

function chargeFeed(){
    feed = new CardGestor(
        "publicaciones",
        renderCard,
        container,
        button,
        10,
        getFilterQueryString(),
        "No hay mas publicaciones"
    );

}

sessionReady.then ( document.addEventListener('DOMContentLoaded', chargeFeed()) );

const applyFiltersBtn = document.getElementById('apply-filters');
if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function () {
        chargeFeed();
    });
}

// --- Lógica para Limpiar Filtros ---
const clearFiltersBtn = document.querySelector('.clear-filters-btn');
if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', function (e) {
        e.preventDefault(); // Prevenir la navegación del enlace
        limpiar_filtros(); // Llama a la nueva función refactorizada
    });
}