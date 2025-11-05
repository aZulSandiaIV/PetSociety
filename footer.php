<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <!-- Sección Sobre Nosotros -->
        <div class="footer-section">
            <h3>🐾 Sobre Nosotros</h3>
            <p>
                <strong>PetSociety</strong> es una plataforma dedicada a conectar a mascotas perdidas con sus familias y facilitar adopciones responsables. 
                Trabajamos junto con refugios de animales para crear una comunidad unida en pro del bienestar animal.
            </p>
            <p>
                Nuestra misión es que ninguna mascota quede sin hogar y que cada animal perdido pueda volver a casa. 
                Creemos en el poder de la tecnología para hacer del mundo un lugar mejor para nuestros compañeros de cuatro patas.
            </p>
        </div>

        <!-- Sección Contacto -->
        <div class="footer-section">
            <h3>📧 Contacto</h3>
            <p>¿Tienes alguna pregunta, sugerencia o necesitas ayuda? ¡Estamos aquí para ti!</p>
            <p>
                <strong>Envíanos un mensaje:</strong><br>
                <a href="contacto.php">💬 Formulario de Contacto</a>
            </p>
            <p>
                Nos encanta escuchar de nuestra comunidad. Ya sea que tengas ideas para mejorar la plataforma, 
                reportes de errores, o simplemente quieras contarnos una historia exitosa de reunión o adopción.
            </p>
        </div>

        <!-- Sección Enlaces Útiles -->
        <div class="footer-section">
            <h3>🔗 Enlaces Útiles</h3>
            <p><a href="index.php">🏠 Inicio</a></p>
            <p><a href="sobre_nosotros.php">ℹ️ Sobre Nosotros</a></p>
            <p><a href="refugios.php">🏥 Refugios Colaboradores</a></p>
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <p><a href="publicar.php">📝 Publicar Mascota</a></p>
                <p><a href="mi_perfil.php">👤 Mi Perfil</a></p>
                <p><a href="buzon.php">📨 Mensajes</a></p>
            <?php else: ?>
                <p><a href="login.php">🔐 Iniciar Sesión</a></p>
                <p><a href="registro.php">📋 Registrarse</a></p>
            <?php endif; ?>
            <p><a href="reportar_avistamiento_mapa.php">📍 Reportar Avistamiento</a></p>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> PetSociety - Plataforma de Reunión y Adopción de Mascotas</p>
        <p>Hecho con ❤️ para nuestros amigos de cuatro patas</p>
    </div>
</footer>