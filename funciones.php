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

/**
 * Genera las iniciales de un nombre
 *
 * @param string $nombre Nombre completo del usuario
 * @return string Iniciales del nombre (máximo 2 caracteres)
 */
function generarIniciales(string $nombre): string
{
    $palabras = explode(' ', trim($nombre));
    $iniciales = '';
    
    foreach ($palabras as $palabra) {
        if (strlen(trim($palabra)) > 0) {
            $iniciales .= strtoupper(substr(trim($palabra), 0, 1));
            if (strlen($iniciales) >= 2) break;
        }
    }
    
    return strlen($iniciales) > 0 ? $iniciales : 'U';
}

/**
 * Genera un color de fondo aleatorio para el avatar
 *
 * @param string $seed Semilla para generar siempre el mismo color para el mismo usuario
 * @return string Color en formato hexadecimal
 */
function generarColorAvatar(string $seed): string
{
    $colores = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
        '#F8C471', '#82E0AA', '#F1948A', '#85C1E9', '#D7BDE2'
    ];
    
    // Usa la semilla para generar siempre el mismo color para el mismo usuario
    $indice = abs(crc32($seed)) % count($colores);
    return $colores[$indice];
}

/**
 * Obtiene la URL de la foto de perfil o genera un avatar con iniciales
 *
 * @param string|null $foto_perfil_url URL de la foto de perfil (puede ser null)
 * @param string $nombre Nombre del usuario para generar iniciales
 * @param int $id_usuario ID del usuario para generar color consistente
 * @return array Array con 'tipo' => 'foto'|'avatar', 'url' => string, 'iniciales' => string, 'color' => string
 */
function obtenerFotoPerfil(?string $foto_perfil_url, string $nombre, int $id_usuario): array
{
    if (!empty($foto_perfil_url) && file_exists($foto_perfil_url)) {
        return [
            'tipo' => 'foto',
            'url' => $foto_perfil_url,
            'iniciales' => '',
            'color' => ''
        ];
    }
    
    return [
        'tipo' => 'avatar',
        'url' => '',
        'iniciales' => generarIniciales($nombre),
        'color' => generarColorAvatar($nombre . $id_usuario)
    ];
}
?>