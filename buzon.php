<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$id_usuario_actual = $_SESSION['id_usuario'];

// Consulta para obtener todos los mensajes donde el usuario es remitente O destinatario.
// Se une la tabla de usuarios dos veces para obtener el nombre tanto del remitente como del destinatario.
$sql = "SELECT 
            m.id_remitente, 
            m.id_destinatario,
            m.asunto, 
            m.contenido, 
            m.fecha_envio, 
            remitente.nombre AS nombre_remitente,
            destinatario.nombre AS nombre_destinatario
        FROM mensajes m
        JOIN usuarios remitente ON m.id_remitente = remitente.id_usuario
        JOIN usuarios destinatario ON m.id_destinatario = destinatario.id_usuario
        WHERE m.id_remitente = ? OR m.id_destinatario = ?
        ORDER BY m.fecha_envio DESC";

$mensajes = [];
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("ii", $id_usuario_actual, $id_usuario_actual);
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
            <div id="branding"><h1><a href="index.php">PetSociety</a></h1></div>
            <nav><ul><li><a href="index.php">Inicio</a></li><li><a href="logout.php">Cerrar Sesión</a></li></ul></nav>
        </div>
    </header>

    <div class="container">
        <h2>Buzón de Entrada</h2>
        <?php if (!empty($mensajes)): ?>
            <?php foreach ($mensajes as $mensaje): ?>
                <div class="mensaje">
                    <?php if ($mensaje['id_destinatario'] == $id_usuario_actual): // Mensaje Recibido ?>
                        <div class="mensaje-header">
                            <strong>De:</strong> <?php echo htmlspecialchars($mensaje['nombre_remitente']); ?><br>
                            <strong>Asunto:</strong> <?php echo htmlspecialchars($mensaje['asunto']); ?><br>
                            <span><strong>Recibido:</strong> <?php echo date("d/m/Y H:i", strtotime($mensaje['fecha_envio'])); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($mensaje['contenido'])); ?></p>
                        <div class="mensaje-actions">
                            <a href="enviar_mensaje.php?reply_to=<?php echo $mensaje['id_remitente']; ?>&asunto=<?php echo urlencode($mensaje['asunto']); ?>" class="btn" style="width: auto;">Responder</a>
                        </div>
                    <?php else: // Mensaje Enviado ?>
                        <div class="mensaje-header" style="background-color: #f9f9f9;">
                            <strong>Para:</strong> <?php echo htmlspecialchars($mensaje['nombre_destinatario']); ?><br>
                            <strong>Asunto:</strong> <?php echo htmlspecialchars($mensaje['asunto']); ?><br>
                            <span><strong>Enviado:</strong> <?php echo date("d/m/Y H:i", strtotime($mensaje['fecha_envio'])); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($mensaje['contenido'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes mensajes nuevos en tu buzón.</p>
        <?php endif; ?>
    </div>
</body>
</html>