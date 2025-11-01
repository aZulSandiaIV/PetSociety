<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";
require_once "funciones.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_remitente = $_SESSION['id_usuario'];
    $id_destinatario = intval($_POST['id_destinatario']);
    $asunto = trim($_POST['asunto']);
    $contenido = trim($_POST['contenido']);

    // Validaciones más detalladas
    if (empty($id_destinatario) || $id_destinatario <= 0) {
        die("Error: ID de destinatario inválido.");
    }
    
    if (empty($asunto)) {
        die("Error: El asunto es obligatorio.");
    }
    
    if (empty($contenido)) {
        die("Error: El contenido del mensaje es obligatorio.");
    }

    // Verificar que el destinatario existe
    $sql_check = "SELECT id_usuario FROM usuarios WHERE id_usuario = ?";
    if ($stmt_check = $conexion->prepare($sql_check)) {
        $stmt_check->bind_param("i", $id_destinatario);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows == 0) {
            die("Error: El destinatario no existe.");
        }
        $stmt_check->close();
    }

    // Usar la función global para enviar el mensaje
    if (enviarMensaje($conexion, $id_remitente, $id_destinatario, $asunto, $contenido)) {
        // redirigir al buzon
        header("location: buzon.php?conversacion=" . $id_destinatario);
        exit;
    } else {
        echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif; color: red;'>";
        echo "<h2>Error al enviar el mensaje</h2>";
        echo "<p>No se pudo enviar el mensaje. Error en la base de datos: " . $conexion->error . "</p>";
        echo "<a href='javascript:history.back()'>Volver e intentar de nuevo</a>";
        echo "</div>";
    }

    $conexion->close();
} else {
    header("location: index.php");
    exit;
}
?>