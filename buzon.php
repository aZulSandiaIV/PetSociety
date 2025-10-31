<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$id_usuario_actual = $_SESSION['id_usuario'];

// Consulta para obtener los mensajes recibidos por el usuario actual
$sql = "SELECT m.id_remitente, m.asunto, m.contenido, m.fecha_envio, u.nombre AS nombre_remitente
        FROM mensajes m
        JOIN usuarios u ON m.id_remitente = u.id_usuario
        WHERE m.id_destinatario = ?
        ORDER BY m.fecha_envio DESC";

$mensajes = [];
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario_actual);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }
    $stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buzón de Entrada - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .mensaje { background: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .mensaje-header { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .mensaje-actions { margin-top: 15px; }
        .mensaje-header span { color: #777; font-size: 0.9em; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo4.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <ul>
                    <li>Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                    <?php endif; ?>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
                    <li><a href="buzon.php">Buzón</a></li>
                    <li><a href="publicar.php">Publicar Animal</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Buzón de Entrada</h2>
        <?php if (!empty($mensajes)): ?>
            <?php foreach ($mensajes as $mensaje): ?>
                <div class="mensaje">
                    <div class="mensaje-header">
                        <strong>De:</strong> <?php echo htmlspecialchars($mensaje['nombre_remitente']); ?><br>
                        <strong>Asunto:</strong> <?php echo htmlspecialchars($mensaje['asunto']); ?><br>
                        <span><strong>Recibido:</strong> <?php echo date("d/m/Y H:i", strtotime($mensaje['fecha_envio'])); ?></span>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($mensaje['contenido'])); ?></p>
                    <div class="mensaje-actions">
                        <a href="enviar_mensaje.php?reply_to=<?php echo $mensaje['id_remitente']; ?>&asunto=<?php echo urlencode($mensaje['asunto']); ?>" class="btn" style="width: auto;">Responder</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes mensajes nuevos en tu buzón.</p>
        <?php endif; ?>
    </div>
</body>
</html>