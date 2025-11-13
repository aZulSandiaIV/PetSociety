
function renderCard(pub){
    return `
    <div class="animal-card" style="position: relative;">
        <span class="refugio-tag">REFUGIO</span>
        <img src="${pub.imagen}" alt="Foto de ${pub.nombre}">
        <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
            <h3>${pub.titulo}</h3>
            <p class="details"><strong>${pub.nombre}</strong> - ${pub.especie}(${pub.raza}</p>
            <p>${pub.contenido_corto}...</p>                                

            <a href="#" class="btn details-btn" onclick='ver_detalles(${JSON.stringify(pub)})'>Ver detalles</a>

            ${pub.estado == 'Perdido' ?
                `<a href="reportar_avistamiento.php?id_animal=${pub.id_animal}" class="btn contact-btn" style="background-color: #E57373;">Reportar Avistamiento</a>`
            :
                `${sessionData.user.id_usuario == pub.id_publicador ?
                    `<span class="btn own-post-indicator">Es tu publicación</span>`
                : sessionData.loggedin == true ?
                    `<a href="enviar_mensaje.php?id_publicacion=${pub.id_publicacion}" class="btn contact-btn">Contactar al Publicador</a>`
                :
                    `<a href="login.php" class="btn contact-btn">Inicia sesión para contactar</a>`
                }`
            }
        </div>
    </div>
    `;
}

sessionReady.then(() => {
    
});

const refugioPublicaciones = new CardGestor(
        'publicaciones',
        renderCard,
        document.getElementById("feed-container"),
        document.getElementById("cargar-mas-btn"),
        20,
        `user_id=${id_refugio}`,
        "no hay mas publicaciones"
);