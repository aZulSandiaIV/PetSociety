<?php
session_start();
require_once "config.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: index.php");
    exit;
}

$id_refugio = intval($_GET['id']);

// --- Obtener datos del refugio ---
$sql_refugio = "SELECT nombre, email, telefono FROM usuarios WHERE id_usuario = ? AND es_refugio = 1";
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
    $stmt_refugio->close();
}

// --- Obtener las publicaciones del refugio ---
$sql_publicaciones = "SELECT p.id_publicacion, p.titulo, p.contenido, a.id_animal, a.nombre, a.estado, a.especie, a.raza, a.imagen_url FROM publicaciones p JOIN animales a ON p.id_animal = a.id_animal WHERE p.id_usuario_publicador = ? ORDER BY p.fecha_publicacion DESC";
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
                <ul>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li>Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                        <?php endif; ?>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
                        <li><a href="buzon.php">Buzón</a></li>
                        <li><a href="publicar.php">Publicar Animal</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="publicar.php" class="btn" style="color:white;padding:5px 10px;">Publicar Animal</a></li>
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
                        <i class="fas fa-home"></i>
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
                    <a href="enviar_mensaje.php?id_destinatario=<?php echo $id_refugio; ?>" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Mensaje
                    </a>
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
                                        <a href="enviar_mensaje.php?id_publicacion=<?php echo $pub['id_publicacion']; ?>" class="btn contact-btn">Contactar al Refugio</a>
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
</body>
</html>
