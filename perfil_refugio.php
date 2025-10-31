<?php
session_start();
require_once "config.php";

// Validar que el ID del refugio esté presente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No se especificó un refugio.");
}
$id_refugio = intval($_GET['id']);

// --- 1. Obtener datos del refugio ---
$sql_refugio = "SELECT nombre, email, telefono, direccion, descripcion, fecha_registro FROM usuarios WHERE id_usuario = ? AND es_refugio = 1";
$refugio = null;
if ($stmt_refugio = $conexion->prepare($sql_refugio)) {
    $stmt_refugio->bind_param("i", $id_refugio);
    $stmt_refugio->execute();
    $result = $stmt_refugio->get_result();
    if ($result->num_rows == 1) {
        $refugio = $result->fetch_assoc();
    } else {
        die("Error: Refugio no encontrado o el usuario no es un refugio.");
    }
    $stmt_refugio->close();
}

// --- 2. Obtener animales publicados por el refugio ---
$sql_animales = "SELECT a.nombre, a.especie, a.raza, a.imagen_url, p.titulo, p.contenido
                 FROM publicaciones p
                 JOIN animales a ON p.id_animal = a.id_animal
                 WHERE p.id_usuario_publicador = ? AND a.estado IN ('En Adopción', 'Hogar Temporal', 'Perdido')
                 ORDER BY p.fecha_publicacion DESC";
$animales = [];
if ($stmt_animales = $conexion->prepare($sql_animales)) {
    $stmt_animales->bind_param("i", $id_refugio);
    $stmt_animales->execute();
    $result_animales = $stmt_animales->get_result();
    while ($row = $result_animales->fetch_assoc()) {
        $animales[] = $row;
    }
    $stmt_animales->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo htmlspecialchars($refugio['nombre']); ?> - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .profile-header {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .profile-header h2 {
            color: #2C5F2D;
            margin-top: 0;
        }
        .profile-contact-info p {
            margin: 5px 0;
            color: #555;
        }
        .profile-contact-info i {
            color: #97BC62;
            margin-right: 10px;
        }
        /* Reutilizamos los estilos de las tarjetas de index.view.php */
        .feed-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .animal-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; }
        .animal-card img { width: 100%; height: 200px; object-fit: cover; }
        .animal-card-content { padding: 15px; }
        .animal-card h3 { margin-top: 0; margin-bottom: 10px; color: #2C5F2D; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <ul>
                    <?php if(isset($_SESSION['loggedin'])): ?>
                        <li>Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                        <?php endif; ?>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
                        <li><a href="buzon.php">Buzón</a></li>
                        <li><a href="publicar.php">Publicar Animal</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="profile-header">
            <h2><?php echo htmlspecialchars($refugio['nombre']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($refugio['descripcion'] ?? 'Este refugio aún no ha añadido una descripción.')); ?></p>
            <div class="profile-contact-info">
                <?php if ($refugio['direccion']): ?><p><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($refugio['direccion']); ?></p><?php endif; ?>
                <?php if ($refugio['email']): ?><p><i class="fas fa-envelope"></i><?php echo htmlspecialchars($refugio['email']); ?></p><?php endif; ?>
                <?php if ($refugio['telefono']): ?><p><i class="fas fa-phone"></i><?php echo htmlspecialchars($refugio['telefono']); ?></p><?php endif; ?>
            </div>
        </div>

        <h3>Animales Publicados por <?php echo htmlspecialchars($refugio['nombre']); ?></h3>
        <div class="feed-container">
            <?php if (!empty($animales)): ?>
                <?php foreach ($animales as $animal): ?>
                    <div class="animal-card">
                        <img src="<?php echo $animal['imagen_url'] ? htmlspecialchars($animal['imagen_url']) : 'https://via.placeholder.com/300x200.png?text=Sin+Foto'; ?>" alt="Foto de <?php echo htmlspecialchars($animal['nombre']); ?>">
                        <div class="animal-card-content">
                            <h3><?php echo htmlspecialchars($animal['titulo']); ?></h3>
                            <p><strong><?php echo htmlspecialchars($animal['nombre']); ?></strong> - <?php echo htmlspecialchars($animal['especie']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Este refugio no tiene publicaciones activas en este momento.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>