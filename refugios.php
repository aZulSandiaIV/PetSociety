<?php
session_start();
require_once "config.php";

// --- Obtener todos los refugios ---
$sql = "SELECT id_usuario, nombre, email, telefono, (SELECT GROUP_CONCAT(p.titulo SEPARATOR ', ') FROM publicaciones p WHERE p.id_usuario_publicador = u.id_usuario) AS publicaciones FROM usuarios u WHERE es_refugio = 1";
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
        <h2>Refugios de Animales</h2>
        <p>Estos son los refugios que colaboran con nosotros. Puedes contactarlos o ver los animales que tienen en adopción.</p>

        <div class="refugios-container">
            <?php if (!empty($refugios)): ?>
                <?php foreach ($refugios as $refugio): ?>
                    <div class="refugio-card">
                        <h3><?php echo htmlspecialchars($refugio['nombre']); ?></h3>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($refugio['email']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($refugio['telefono'] ?? 'No especificado'); ?></p>
                        <p><strong>Publicaciones:</strong> <?php echo htmlspecialchars($refugio['publicaciones'] ?? 'Sin publicaciones'); ?></p>
                        <div class="refugio-actions">
                            <a href="perfil_refugio.php?id=<?php echo $refugio['id_usuario']; ?>" class="btn">Ver Perfil</a>
                            <a href="enviar_mensaje.php?id_destinatario=<?php echo $refugio['id_usuario']; ?>" class="btn">Enviar Mensaje</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay refugios registrados por el momento.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
