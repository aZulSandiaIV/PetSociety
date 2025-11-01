<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$destinatario_id = 0;
$titulo_publicacion = '';
$nombre_publicador = '';
$asunto_sugerido = '';

// --- Lógica para determinar el destinatario y el asunto ---

// CASO 1: Es una respuesta a un mensaje
if (isset($_GET['reply_to']) && !empty($_GET['reply_to'])) {
    $destinatario_id = intval($_GET['reply_to']);
    $sql = "SELECT nombre FROM usuarios WHERE id_usuario = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $destinatario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $nombre_publicador = $row['nombre'];
        } else {
            die("Error: Destinatario no encontrado.");
        }
        $stmt->close();
    }
    // Preparar el asunto para la respuesta
    $asunto_original = urldecode($_GET['asunto'] ?? '');
    if (strpos($asunto_original, 'Re: ') !== 0) {
        $asunto_sugerido = 'Re: ' . $asunto_original;
    } else {
        $asunto_sugerido = $asunto_original;
    }

// CASO 2: Es un mensaje nuevo desde una publicación
} elseif (isset($_GET['id_publicacion']) && !empty($_GET['id_publicacion'])) {
    $id_publicacion = intval($_GET['id_publicacion']);
    $sql = "SELECT p.titulo, p.id_usuario_publicador, a.nombre AS nombre_animal, u.nombre AS nombre_publicador
            FROM publicaciones p
            JOIN animales a ON p.id_animal = a.id_animal
            JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario
            WHERE p.id_publicacion = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $id_publicacion);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $destinatario_id = $row['id_usuario_publicador'];
            $titulo_publicacion = $row['titulo'];
            $nombre_publicador = $row['nombre_publicador'];
            $asunto_sugerido = "Interés en la publicación: " . htmlspecialchars($row['titulo']) . " (" . htmlspecialchars($row['nombre_animal']) . ")";
        } else {
            die("Error: Publicación no encontrada.");
        }
        $stmt->close();
    }

// CASO 3: Es un mensaje directo a un usuario específico
} elseif (isset($_GET['id_destinatario']) && !empty($_GET['id_destinatario'])) {
    $destinatario_id = intval($_GET['id_destinatario']);
    $sql = "SELECT nombre FROM usuarios WHERE id_usuario = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $destinatario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $nombre_publicador = $row['nombre'];
            $asunto_sugerido = "Mensaje para " . htmlspecialchars($row['nombre']);
        } else {
            die("Error: Destinatario no encontrado.");
        }
        $stmt->close();
    }
} else {
    die("Error: No se especificó una publicación o un destinatario.");
}

// No permitir que un usuario se envíe un mensaje a sí mismo
if ($destinatario_id == $_SESSION['id_usuario']) {
    die("No puedes enviarte un mensaje a ti mismo. <a href='index.php'>Volver</a>");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Mensaje - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <ul>
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
                </ul>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <h2>Enviar Mensaje a <?php echo htmlspecialchars($nombre_publicador); ?></h2>
        <?php if (!empty($titulo_publicacion)): ?>
            <p>Referente a la publicación: "<?php echo htmlspecialchars($titulo_publicacion); ?>"</p>
        <?php endif; ?>
        <form action="procesar_mensaje.php" method="post">
            <input type="hidden" name="id_destinatario" value="<?php echo $destinatario_id; ?>">
            <div class="form-group"><label>Asunto</label><input type="text" name="asunto" value="<?php echo htmlspecialchars($asunto_sugerido); ?>" required></div>
            <div class="form-group"><label>Mensaje</label><textarea name="contenido" rows="8" required placeholder="Escribe tu mensaje aquí..."></textarea></div>
            <div class="form-group"><input type="submit" class="btn" value="Enviar Mensaje"></div>
            <a href="index.php">Cancelar y volver</a>
        </form>
    </div>
</body>
</html>