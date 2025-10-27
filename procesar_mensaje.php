<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_remitente = $_SESSION['id_usuario'];
    $id_destinatario = intval($_POST['id_destinatario']);
    $asunto = trim($_POST['asunto']);
    $contenido = trim($_POST['contenido']);

    if (empty($id_destinatario) || empty($asunto) || empty($contenido)) {
        die("Error: Todos los campos son obligatorios.");
    }

    // Usar la función global para enviar el mensaje
    if (enviarMensaje($conexion, $id_remitente, $id_destinatario, $asunto, $contenido)) {
        echo "¡Mensaje enviado con éxito! Serás redirigido en 3 segundos.";
        header("refresh:3;url=index.php");
    } else {
        echo "Error al enviar el mensaje. Por favor, inténtalo de nuevo.";
    }

    $conexion->close();
} else {
    header("location: index.php");
    exit;
}
?>