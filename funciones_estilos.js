/**
 * Agrega la funcionalidad de mostrar/ocultar contraseña a un par de elementos (botón e input).
 * @param {string} botonId - El ID del botón que funciona como toggle.
 * @param {string} inputId - El ID del campo de contraseña.
 */
function asociarToggleContraseña(botonId, inputId) {
    const boton = document.getElementById(botonId);
    const input = document.getElementById(inputId);

    if (boton && input) {
        boton.addEventListener('click', function() {
            if (input.type === "password") {
                input.type = "text";
                // Cambiamos el texto del botón a un emoji de "ojo cerrado"
                this.textContent = '🙈';
            } else {
                input.type = "password";
                // Volvemos al emoji de "ojo abierto"
                this.textContent = '👁️';
            }
        });

        // Para que el botón se vea como un ojo desde el principio,
        // le asignamos el emoji al cargar la página.
        // Usamos un pequeño retraso para asegurar que el DOM esté listo.
        setTimeout(() => {
            boton.textContent = '👁️';
        }, 0);
    }
}