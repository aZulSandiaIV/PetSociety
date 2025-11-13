<?php
session_start();
require_once "config.php";
require_once "funciones.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: index.php");
    exit;
}

$id_refugio = intval($_GET['id']);

// --- Obtener datos del refugio usando la nueva función ---
$refugio = obtener_datos_refugio($conexion, $id_refugio);
if ($refugio === null) {
    // No se encontró el refugio o no es un refugio, redirigir
    header("location: refugios.php");
    exit;
}

// --- Obtener las publicaciones del refugio usando la función refactorizada ---
$publicaciones = obtener_publicaciones($conexion, ['id_publicador' => $id_refugio]);

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
    <script>
        const id_refugio = <?php echo $id_refugio; ?>
    </script>
    <script src = "js/CargaAsync.js"></script>
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
                    <?php elseif (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION['id_usuario'] == $id_refugio): ?>
                        <span class="btn own-post-indicator" style="cursor: default;">Es tu perfil</span>
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

            <div id="feed-container" class="feed-container">
                <!-- Aca cargo de forma dinamica -->
            </div>
            <!--
                <div class="no-publications">
                    <i class="fas fa-info-circle"></i>
                    <p>Este refugio aún no tiene publicaciones.</p>
                </div>
                    -->
            <div style="display: flex; justify-content: space-evenly;">
                <button id = "cargar-mas-btn" class = "btn">Cargar mas</button>
            </div>
            
        </div>
    </div>

    <!-- MODAL PARA VER DETALLES DEL ANIMAL (copiado de index.view.php) -->
    <div id="modal-detalles" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
            <div class="modal-body">
                <img id="modal-imagen" src="" alt="Foto del animal" class="modal-img">
                <div class="modal-info">
                    <h2 id="modal-titulo"></h2>
                    <p><strong>Nombre:</strong> <span id="modal-nombre"></span></p>
                    <p><strong>Especie:</strong> <span id="modal-especie"></span></p>
                    <p><strong>Raza:</strong> <span id="modal-raza"></span></p>
                    <p><strong>Género:</strong> <span id="modal-genero"></span></p>
                    <p><strong>Edad:</strong> <span id="modal-edad"></span></p>
                    <p><strong>Tamaño:</strong> <span id="modal-tamano"></span></p>
                    <p><strong>Color:</strong> <span id="modal-color"></span></p>
                    <hr>
                    <h3>Descripción</h3>
                    <p id="modal-descripcion"></p>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: #fff; padding: 30px; border-radius: 10px; max-width: 800px; width: 90%; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-close { position: absolute; top: 10px; right: 15px; font-size: 28px; font-weight: bold; border: none; background: none; cursor: pointer; }
        .modal-body { display: flex; gap: 20px; }
        .modal-img { width: 300px; height: 300px; object-fit: cover; border-radius: 8px; }
        .modal-info h2 { margin-top: 0; }
        .modal-info p { margin: 8px 0; }
        @media (max-width: 768px) {
            .modal-body { flex-direction: column; }
            .modal-img { width: 100%; height: 250px; }
        }
        .animal-card .details-btn {
            margin-top: 8px;
            background-color: #f0f0f0;
            color: #404040;
            text-align: center;
        }
        .animal-card .details-btn:hover {
            background-color: #e0e0e0;
            color: #202020;
        }
    </style>
    
    <script src="funciones_js.js"></script>
    <script>
        // Llama a la función para la interactividad de los menús
        interactividad_menus();
    </script>

    <!-- Scripts para el modal -->
    
    <script>
        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modal-detalles');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
    <script src = "js/perfil_refugio.js"></script>
    
    <?php include 'footer.php'; ?>
</body>
</html>
