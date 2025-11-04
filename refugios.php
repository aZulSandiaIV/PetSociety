<?php
session_start();
require_once "config.php";
require_once "funciones.php";

// --- Obtener todos los refugios ---
$sql = "SELECT id_usuario, nombre, email, telefono, foto_perfil_url, (SELECT GROUP_CONCAT(p.titulo SEPARATOR ', ') FROM publicaciones p WHERE p.id_usuario_publicador = u.id_usuario) AS publicaciones FROM usuarios u WHERE es_refugio = 1";
$refugios = [];
if ($result = $conexion->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $refugios[] = $row;
    }
    $result->free();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Refugios - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="refugio.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    
</head>
<body>
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
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="buzon.php">Mensajes</a></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li class="admin-panel-dropdown">
                                <span class="admin-panel-trigger">Panel de Administrador</span>
                                <div class="admin-submenu">
                                    <ul>
                                        <li><a href="admin/statistics.php">Estadísticas</a></li>
                                        <li><a href="admin/manage_publications.php">Administrar Publicaciones</a></li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
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

    <div class="container">
        <h2>Refugios de Animales</h2>
        <p>Estos son los refugios que colaboran con nosotros. Puedes contactarlos o ver los animales que tienen en adopción.</p>

        <div class="refugios-container">
            <?php if (!empty($refugios)): ?>
                <?php foreach ($refugios as $refugio): ?>
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
                        <p><strong>Publicaciones:</strong> <?php echo htmlspecialchars($refugio['publicaciones'] ?? 'Sin publicaciones'); ?></p>
                        <div class="refugio-actions">
                            <a href="perfil_refugio.php?id=<?php echo $refugio['id_usuario']; ?>" class="btn btn-ver-perfil">Ver Perfil</a>
                            <a href="enviar_mensaje.php?id_destinatario=<?php echo $refugio['id_usuario']; ?>" class="btn btn-enviar-mensaje">📩 Enviar Mensaje</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay refugios registrados por el momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const mobileUserMenu = document.querySelector('.mobile-user-menu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });

                document.addEventListener('click', function(event) {
                    if (!navMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    }
                });

                const navLinks = navMenu.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    });
                });
            }

            if (mobileUserMenu) {
                const userMenuTrigger = mobileUserMenu.querySelector('.user-menu-trigger');
                if (userMenuTrigger) {
                    userMenuTrigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        mobileUserMenu.classList.toggle('active');
                    });
                }
            }
        });
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>
