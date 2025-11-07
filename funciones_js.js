function ver_detalles(animal) {
    // Llenar el modal con los datos del animal
    document.getElementById('modal-imagen').src = animal.imagen;
    document.getElementById('modal-titulo').textContent = animal.titulo;
    document.getElementById('modal-nombre').textContent = animal.nombre;
    document.getElementById('modal-especie').textContent = animal.especie;
    document.getElementById('modal-raza').textContent = animal.raza || 'No especificada';
    document.getElementById('modal-genero').textContent = animal.genero || 'No especificado';
    document.getElementById('modal-edad').textContent = animal.edad || 'No especificada';
    document.getElementById('modal-tamano').textContent = animal.tamano || 'No especificado';
    document.getElementById('modal-color').textContent = animal.color || 'No especificado';
    document.getElementById('modal-descripcion').innerHTML = animal.descripcion; // Usamos innerHTML por si tiene saltos de línea

    // Mostrar el modal
    document.getElementById('modal-detalles').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-detalles').style.display = 'none';
}