const button = document.getElementById('cargar-mas-btn');
const container = document.getElementById('refugios-container');

let cargar_apartir = 0;
let cargar_cantidad = 20;

function renderCard(refugio){
    return `
    <div class="refugio-card">
        <div class="refugio-profile-pic">
            ${refugio.foto.tipo === 'foto' ?
                `<img src="${refugio.foto.url}" alt="Foto de perfil de ${refugio.nombre}">`
            :
                `<div class="refugio-avatar" style="background-color: ${refugio.foto.color};">
                    ${refugio.foto.iniciales}
                </div>`
            }
        </div>
        <h3>${refugio.nombre}</h3>
        <p><strong>Email:</strong> ${refugio.email}</p>
        <p><strong>Teléfono:</strong> ${refugio.telefono ?? 'No especificado'}</p>
        <p><strong>Publicaciones activas:</strong> ${refugio.num_publicaciones}</p>
        <div class="refugio-actions">
            <a href="perfil_refugio.php?id=${refugio.id_usuario}" class="btn btn-ver-perfil">Ver Perfil</a>
            ${sessionData.loggedin && sessionData.user.id_usuario != refugio.id_usuario ?
                `<a href="enviar_mensaje.php?id_destinatario=${refugio.id_usuario}" class="btn btn-enviar-mensaje">📩 Enviar Mensaje</a>`
            :
                ''
            }
        </div>
    </div>
    `;
}

document.addEventListener('DOMContentLoaded', function() {

    

    let filtro = `?cargar_apartir=${cargar_apartir}&cargar_cantidad=${cargar_cantidad}`;
    mostrar_publicaciones('refugios', renderCard, container, filtro);
    cargar_apartir += cargar_cantidad;

    button.addEventListener('click', function() {
        filtro = `?cargar_apartir=${cargar_apartir}&cargar_cantidad=${cargar_cantidad}`;
        mostrar_publicaciones('refugios', renderCard, container, filtro);
        cargar_apartir += cargar_cantidad;
    });

});