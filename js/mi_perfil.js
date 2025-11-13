
const container = document.getElementById('publications-section');
const button = document.getElementById('cargar-mas-btn');
let cargar_cantidad = 20;
let cargar_desde = 0;

function renderCard(pub) {
    const esc = (v) => String(v ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');

    const estado = String(pub['estado'] || '');
    const estadoClass = (estado === 'Adoptado' || estado === 'Encontrado')
        ? 'estado-' + estado.replace(/\s+/g, '-').toLowerCase()
        : '';

    return `
    <div class="pub-card">
        <div class="pub-info"> 
            <h3>${esc(pub['titulo'])} (${esc(pub['nombre'])})</h3>
            <span class="pub-estado ${estadoClass}">
                Estado: ${esc(estado)}
            </span>
        </div>
        <div class="pub-actions">
            ${
                // --- BLOQUE 1: Botones de Acción Principal ---
                estado === 'En Adopción' ? `
                    <a href="gestionar_adopcion.php?id_pub=${encodeURIComponent(pub['id_publicacion'])}&id_animal=${encodeURIComponent(pub['id_animal'])}" class="btn">Gestionar Adopción</a>
                ` :
                estado === 'Hogar Temporal' ? `
                    <a href="gestionar_adopcion.php?id_pub=${encodeURIComponent(pub['id_publicacion'])}&id_animal=${encodeURIComponent(pub['id_animal'])}" class="btn">Gestionar Hogar</a>
                ` :
                estado === 'Perdido' ? `
                    <form method="post" style="display: inline;">
                        <a href="ver_reportes.php?id_animal=${encodeURIComponent(pub['id_animal'])}" class="btn" style="background-color: #97BC62;">Ver Avistamientos</a>
                        <input type="hidden" name="id_animal" value="${esc(pub['id_animal'])}">
                        <button type="submit" name="marcar_encontrado" class="btn" onclick="return confirm('¿Estás seguro de que has encontrado a tu mascota?');">Marcar como Encontrado</button>
                    </form>
                ` :
                estado === 'Encontrado' ? `
                    <span>Publicación activa</span>
                ` : `
                    <span>Gestión finalizada</span>
                `
            }

            ${ 
                // --- BLOQUE 2: Botón de Editar (Corregido) ---
                (estado !== 'Adoptado' && estado !== 'Encontrado') ? `
                    <a href="editar_publicacion.php?id=${encodeURIComponent(pub['id_publicacion'])}" class="btn" style="background-color: #f0ad4e; margin-left: 5px;">Editar</a>
                ` : '' 
            }
        </div>
    </div>
    `;
}
// No hay equivalente a htmlspecialchars en JS, se asume que los datos ya están sanitizados al venir del servidor

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('publications-section');
    const button = document.getElementById('cargar-mas-btn');
    //const eventSession = new EventSource("solicitar/session_check.php");
    if (!container) {
        console.warn('mi_perfil: contenedor "publications-section" no encontrado en el DOM.');
        return;
    }

    //Window.onload espera a que TODO este cargado, eso incluye el session_check.php
    
    window.onload = function() {
        mostrar_publicaciones('publicaciones', renderCard, container, `?cargar_cantidad=${cargar_cantidad}&cargar_apartir=${cargar_desde}&user_id=${sessionData['user']['id_usuario']}`);
        cargar_desde += cargar_cantidad;
    }
    /*
    eventSession.addEventListener("message", function(){
        
    });
    */
});

button.addEventListener('click', function() {
    mostrar_publicaciones('publicaciones', renderCard, container, `?cargar_cantidad=${cargar_cantidad}&cargar_apartir=${cargar_desde}&user_id=${sessionData['user']['id_usuario']}`);
    cargar_desde += cargar_cantidad;
});


