var CargarIncremento = 5;
var CargarApartirDe = 0;

var botton = document.getElementById("cargar-mas");
    botton.addEventListener("click", mostrar_publicaciones);

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
        
        const data = await response.json();

        return data;

    } catch (error) {
        console.error('Error al obtener los datos:', error);
    }
}

function mostrar_publicaciones() {
    container = document.getElementsByClassName('feed-container')[0];

    cargar_publicaciones().then(data => {
        if(!data || data.length === 0) return;

        data.forEach(animal => {
            
            container.innerHTML += `
                <div class="animal-card" style="position: relative;">
                    ${animal.es_refugio == 1 ? "<span class='refugio-tag'>REFUGIO</span>" : ''}
                    
                    <img src="${animal.imagen}" alt="Foto de ${animal.nombre}">

                    <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
                        <h3>${animal.titulo}</h3>
                        <p class="details"><strong>${animal.nombre}</strong> - ${animal.especie} (${animal.raza})</p>
                        <p>${animal.contenido_corto}...</p>
                    </div>
                </div>
            `;
        });
    });

    CargarApartirDe += CargarIncremento;
}

