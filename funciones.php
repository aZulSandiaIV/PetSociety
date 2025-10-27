<?php
/**
 * Fichero para funciones reutilizables en la aplicación.
 */

/**
 * Envía un mensaje de un usuario a otro y lo guarda en la base de datos.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @param int $id_remitente ID del usuario que envía el mensaje.
 * @param int $id_destinatario ID del usuario que recibirá el mensaje.
 * @param string $asunto Asunto del mensaje.
 * @param string $contenido Cuerpo del mensaje.
 * @return bool Devuelve true si el mensaje se envió correctamente, false en caso de error.
 */
function enviarMensaje(mysqli $conexion, int $id_remitente, int $id_destinatario, string $asunto, string $contenido): bool 
{
    $sql = "INSERT INTO mensajes (id_remitente, id_destinatario, asunto, contenido) VALUES (?, ?, ?, ?)";

    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("iiss", $id_remitente, $id_destinatario, $asunto, $contenido);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
    }
    // Si algo falla, devuelve false.
    return false;
}
?>