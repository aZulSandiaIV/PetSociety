<header>
    <div class="container">
        <div id="branding">
            <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
        </div>
        <nav>
            <button class="mobile-menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="mapa.php">Mapa</a></li>
                    <li><a href="refugios.php">Refugios</a></li>
                    <li id = "buzon-header">
                        <a href="buzon.php">
                            Mensajes
                        </a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li class="admin-panel-dropdown">
                            <span class="admin-panel-trigger">Panel de Administrador</span>
                            <div class="admin-submenu">
                                <ul>
                                    <li><a href="admin/statistics.php">Estadísticas</a></li>
                                    <li><a href="admin/manage_users.php">Administrar Usuarios</a></li>
                                    <li><a href="admin/manage_publications.php">Administrar Publicaciones</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="mapa.php">Mapa</a></li>
                    <li><a href="refugios.php">Refugios</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li class="user-menu mobile-user-menu">
                        <span class="user-menu-trigger">
                            <span class="user-icon"></span>
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                        </span>
                        <div class="dropdown-menu">
                            <ul>
                                <li><a href="mi_perfil.php">Mi Perfil</a></li>
                                <li><a href="logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buzonHeader = document.getElementById('buzon-header');
            
            if (!buzonHeader) {
                console.error('Elemento buzon-header no encontrado');
                return;
            }

            fetch(`solicitar/notificaciones.php?id_usuario=<?php echo (int)$_SESSION['id_usuario']; ?>`)
                .then(response => response.json())
                .then(notificaciones => {
                    if (notificaciones && notificaciones.count > 0) {
                        buzonHeader.innerHTML += '<img src="img/notificacion.png" alt="Icono Buzón" class="icono-buzón-header" style="width: 17px;vertical-align: middle;">';
                    }
                })
                .catch(error => console.error('Error en notificaciones:', error));
        });
    </script>
<?php endif; ?>