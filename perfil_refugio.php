<?php
session_start();
require_once "config.php";
require_once "funciones.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: index.php");
    exit;
}

$id_refugio = intval($_GET['id']);

// --- Obtener datos del refugio ---
$sql_refugio = "SELECT nombre, email, telefono, foto_perfil_url FROM usuarios WHERE id_usuario = ? AND es_refugio = 1";
$refugio = null;
if ($stmt_refugio = $conexion->prepare($sql_refugio)) {
    $stmt_refugio->bind_param("i", $id_refugio);
    $stmt_refugio->execute();
    $result_refugio = $stmt_refugio->get_result();
    if ($result_refugio->num_rows == 1) {
        $refugio = $result_refugio->fetch_assoc();
    } else {
        // No se encontró el refugio, redirigir
        header("location: refugios.php");
        exit;
    }
    $result_refugio->free(); // Liberar el resultado de la consulta anterior
    $stmt_refugio->close();
}

// --- Obtener las publicaciones del refugio ---
$sql_publicaciones = "SELECT p.id_publicacion, p.titulo, p.contenido, a.id_animal, a.nombre, a.estado, a.especie, a.raza, a.imagen_url 
                      FROM publicaciones p 
                      JOIN animales a ON p.id_animal = a.id_animal 
                      WHERE p.id_usuario_publicador = ? 
                      AND a.estado NOT IN ('Adoptado', 'Encontrado')
                      ORDER BY p.fecha_publicacion DESC";
$publicaciones = [];
if ($stmt_publicaciones = $conexion->prepare($sql_publicaciones)) {
    $stmt_publicaciones->bind_param("i", $id_refugio);
    $stmt_publicaciones->execute();
    $result_publicaciones = $stmt_publicaciones->get_result();
    while ($row = $result_publicaciones->fetch_assoc()) {
        $publicaciones[] = [
            'id_publicacion' => $row['id_publicacion'],
            'id_animal' => $row['id_animal'],
            'titulo' => htmlspecialchars($row['titulo']),
            'nombre' => htmlspecialchars($row['nombre']),
            'estado' => htmlspecialchars($row['estado']),
            'especie' => htmlspecialchars($row['especie']),
            'raza' => htmlspecialchars($row['raza']),
            'imagen' => $row['imagen_url'] ? htmlspecialchars($row['imagen_url']) : 'https://via.placeholder.com/300x200.png?text=Sin+Foto',
            'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100)))
        ];
    }
    $stmt_publicaciones->close();
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil del Refugio - PetSociety</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="refugio.css">
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
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                        <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                                    <?php endif; ?>
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
        <!-- Sección del perfil del refugio -->
        <div class="refugio-profile-header">
            <div class="refugio-profile-card">
                <div class="refugio-profile-info">
                    <div class="refugio-icon">
                        <?php 
                        $foto_perfil = obtenerFotoPerfil($refugio['foto_perfil_url'], $refugio['nombre'], $id_refugio);
                        if ($foto_perfil['tipo'] === 'foto'): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil['url']); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($refugio['nombre']); ?>" class="refugio-profile-image">
                        <?php else: ?>
                            <div class="refugio-profile-avatar" style="background-color: <?php echo $foto_perfil['color']; ?>">
                                <?php echo htmlspecialchars($foto_perfil['iniciales']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="refugio-details">
                        <h1 class="refugio-name"><?php echo htmlspecialchars($refugio['nombre']); ?></h1>
                        <div class="refugio-contact">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($refugio['email']); ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($refugio['telefono'] ?? 'No especificado'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="refugio-actions">
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION['id_usuario'] != $id_refugio): ?>
                        <a href="enviar_mensaje.php?id_destinatario=<?php echo $id_refugio; ?>" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Mensaje
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sección de publicaciones -->
        <div class="refugio-publications-section">
            <h2 class="publications-title">
                <i class="fas fa-paw"></i>
                Animales del Refugio
            </h2>
            <?php if (!empty($publicaciones)): ?>
                <div class="feed-container">
                    <?php foreach ($publicaciones as $pub): ?>
                        <div class="animal-card" style="position: relative;">
                            <span class="refugio-tag">REFUGIO</span>
                            <img src="<?php echo $pub['imagen']; ?>" alt="Foto de <?php echo $pub['nombre']; ?>">
                            <div class="animal-card-content" style="display: flex; flex-direction: column; flex-grow: 1;">
                                <h3><?php echo $pub['titulo']; ?></h3>
                                <p class="details"><strong><?php echo $pub['nombre']; ?></strong> - <?php echo $pub['especie']; ?> (<?php echo $pub['raza']; ?>)</p>
                                <p><?php echo $pub['contenido_corto']; ?>...</p>
                                <?php if ($pub['estado'] == 'Perdido'): ?>
                                    <a href="reportar_avistamiento.php?id_animal=<?php echo $pub['id_animal']; ?>" class="btn contact-btn" style="background-color: #E57373;">Reportar Avistamiento</a>
                                <?php else: ?>
                                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                        <a href="enviar_mensaje.php?id_publicacion=<?php echo $pub['id_publicacion']; ?>" class="btn contact-btn">Contactar al Publicador</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn contact-btn">Inicia sesión para contactar</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-publications">
                    <i class="fas fa-info-circle"></i>
                    <p>Este refugio aún no tiene publicaciones.</p>
                </div>
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
