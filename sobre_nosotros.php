<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="sobre_nosotros.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="refugios.php">Refugios</a></li>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li><a href="buzon.php">Mensajes</a></li>
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
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="about-section">
            <h1><i class="fas fa-heart"></i> Sobre PetSociety</h1>
            <p style="font-size: 1.2rem; line-height: 1.8; color: #404040;">
                <strong>PetSociety</strong> nace del amor hacia nuestros compañeros de cuatro patas y la necesidad de crear 
                una comunidad unida que trabaje por el bienestar animal. Somos una plataforma tecnológica dedicada a 
                <strong>reunir mascotas perdidas con sus familias</strong> y <strong>facilitar adopciones responsables</strong>.
            </p>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-bullseye"></i> Nuestra Misión</h2>
            <p>
                Creemos firmemente que <strong>ninguna mascota debería quedar sin hogar</strong> y que cada animal perdido 
                merece la oportunidad de volver a casa. Nuestra misión es utilizar la tecnología para:
            </p>
            <div class="feature-list">
                <div class="feature-item">
                    <h4><i class="fas fa-search"></i> Facilitar Búsquedas</h4>
                    <p>Conectar rápidamente a mascotas perdidas con sus familias a través de publicaciones y mapas de avistamientos.</p>
                </div>
                <div class="feature-item">
                    <h4><i class="fas fa-home"></i> Promover Adopciones</h4>
                    <p>Ayudar a que animales en refugios encuentren familias amorosas y responsables.</p>
                </div>
                <div class="feature-item">
                    <h4><i class="fas fa-users"></i> Construir Comunidad</h4>
                    <p>Crear una red de personas comprometidas con el bienestar animal en su comunidad.</p>
                </div>
                <div class="feature-item">
                    <h4><i class="fas fa-shield-alt"></i> Garantizar Seguridad</h4>
                    <p>Proporcionar herramientas seguras para la comunicación entre adoptantes y refugios.</p>
                </div>
            </div>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-star"></i> Lo Que Ofrecemos</h2>
            <div class="feature-list">
                <div class="feature-item">
                    <h4><i class="fas fa-camera"></i> Publicaciones de Mascotas</h4>
                    <p>Sistema completo para publicar mascotas perdidas, encontradas o en adopción con fotos y descripciones detalladas.</p>
                </div>
                <div class="feature-item">
                    <h4><i class="fas fa-map-marked-alt"></i> Mapa de Avistamientos</h4>
                    <p>Herramienta geolocalizada para reportar y visualizar avistamientos de animales perdidos en tiempo real.</p>
                </div>
                <div class="feature-item">
                    <h4><i class="fas fa-building"></i> Red de Refugios</h4>
                    <p>Plataforma dedicada para refugios de animales para mostrar sus mascotas disponibles para adopción.</p>
                </div>
                <div class="feature-item">
                    <h4><i class="fas fa-envelope"></i> Sistema de Mensajería</h4>
                    <p>Comunicación segura entre usuarios, adoptantes y refugios para facilitar el proceso de reunión y adopción.</p>
                </div>
            </div>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-handshake"></i> Únete a Nuestra Comunidad</h2>
            <p>
                PetSociety es más que una plataforma; es una <strong>comunidad de personas</strong> que creen en el poder 
                del amor hacia los animales. Ya seas dueño de una mascota, voluntario de refugio, o simplemente alguien 
                que se preocupa por el bienestar animal, hay un lugar para ti aquí.
            </p>
            <div style="text-align: center; margin-top: 30px;">
                <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                    <a href="registro.php" class="btn" style="background-color: #6b8e9f; padding: 15px 30px; font-size: 1.1rem;">
                        <i class="fas fa-user-plus"></i> Únete Ahora
                    </a>
                <?php endif; ?>
                <a href="contacto.php" class="btn" style="background-color: #404040; padding: 15px 30px; font-size: 1.1rem; margin-left: 10px;">
                    <i class="fas fa-envelope"></i> Contáctanos
                </a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="funciones_js.js"></script>
    <script>
        // Activar funcionalidad del menú hamburguesa
        interactividad_menus();
    </script>
</body>
</html>