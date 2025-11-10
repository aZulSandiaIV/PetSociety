let cargar_apartir = 0;
let cargar_cantidad = 5;

function renderCard(refugio){
    return `
    <div class="refugio-card">
        <?php 
        $foto_perfil = obtenerFotoPerfil($refugio['foto_perfil_url'], $refugio['nombre'], $refugio['id_usuario']);
        ?>
        <div class="refugio-profile-pic">
            <?php if ($foto_perfil['tipo'] === 'foto'): ?>
                <img src="<?php echo htmlspecialchars($foto_perfil['url']); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($refugio['nombre']); ?>">
            <?php else: ?>
                <div class="refugio-avatar" style="background-color: <?php echo $foto_perfil['color']; ?>">
                    <?php echo htmlspecialchars($foto_perfil['iniciales']); ?>
                </div>
            <?php endif; ?>
        </div>
        <h3><?php echo htmlspecialchars($refugio['nombre']); ?></h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($refugio['email']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($refugio['telefono'] ?? 'No especificado'); ?></p>
        <p><strong>Publicaciones activas:</strong> <?php echo $refugio['num_publicaciones']; ?></p>
        <div class="refugio-actions">
            <a href="perfil_refugio.php?id=<?php echo $refugio['id_usuario']; ?>" class="btn btn-ver-perfil">Ver Perfil</a>
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION['id_usuario'] != $refugio['id_usuario']): ?>
                <a href="enviar_mensaje.php?id_destinatario=<?php echo $refugio['id_usuario']; ?>" class="btn btn-enviar-mensaje">📩 Enviar Mensaje</a>
            <?php endif; ?>
        </div>
    </div>
    `;
}

document.addEventListener('DOMContentLoaded', function() {

    const button = document.getElementById('cargar-mas-btn');
    const container = document.getElementById('refugios-container');

    mostrar_publicaciones('refugios', renderCard, container);

});