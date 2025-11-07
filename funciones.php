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

/**
 * Verifica si un usuario es administrador consultando la base de datos
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos
 * @param int $id_usuario ID del usuario a verificar
 * @return bool true si el usuario es administrador (is_admin = 1), false en caso contrario
 */
function esAdministrador(mysqli $conexion, int $id_usuario): bool
{
    $sql = "SELECT is_admin FROM usuarios WHERE id_usuario = ?";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return (bool)$row['is_admin'];
        }
        $stmt->close();
    }
    
    return false;
}

/**
 * Actualiza la sesión con el estado de administrador del usuario
 * 
 * @param mysqli $conexion Objeto de conexión a la base de datos
 * @param int $id_usuario ID del usuario
 * @return void
 */
function actualizarSesionAdmin(mysqli $conexion, int $id_usuario): void
{
    if (esAdministrador($conexion, $id_usuario)) {
        $_SESSION['is_admin'] = 1;
    } else {
        $_SESSION['is_admin'] = 0;
    }
}

/**
 * Obtiene los datos para todos los marcadores del mapa.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @param array $animales Array de animales ya cargados en la página.
 * @return array Un array con 'avistamientos_json', 'perdidos_json', y 'publicaciones_json'.
 */
function mapa_avistamientos(mysqli $conexion, array $animales): array
{
    $map_data = [
        'avistamientos_json' => '[]',
        'perdidos_json' => '[]',
        'publicaciones_json' => '[]'
    ];

    // --- LÓGICA PARA EL MAPA DE AVISTAMIENTOS ---
    $sql_avistamientos = "SELECT id_avistamiento, latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
                          FROM avistamientos 
                          WHERE estado = 'Visto'
                          ORDER BY fecha_avistamiento DESC
                          LIMIT 50";

    if ($result_avistamientos = $conexion->query($sql_avistamientos)) {
        $avistamientos_mapa = [];
        while ($row = $result_avistamientos->fetch_assoc()) {
            $botones_avistamiento = '';
            if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
                $botones_avistamiento = "
                    <div style='margin-top:10px; display:flex; justify-content:space-around;'>
                        <a href='actualizar_avistamiento.php?id={$row['id_avistamiento']}&accion=sigue_aqui' style='font-size:0.8em; padding: 4px 8px; background-color:#97BC62; color:white; text-decoration:none; border-radius:4px;'>Sigue Aquí</a>
                        <a href='actualizar_avistamiento.php?id={$row['id_avistamiento']}&accion=no_esta' style='font-size:0.8em; padding: 4px 8px; background-color:#E57373; color:white; text-decoration:none; border-radius:4px;'>Ya no está</a>
                    </div>";
            }
            $row['popup_html'] = "
                <div>
                    <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Avistamiento' style='width:150px; height:auto; border-radius:4px;'>
                    <p>" . htmlspecialchars($row['descripcion']) . "</p>
                    <small>Visto el: " . date('d/m/Y H:i', strtotime($row['fecha_avistamiento'])) . "</small>
                    {$botones_avistamiento}
                </div>
            ";
            $avistamientos_mapa[] = $row;
        }
        $map_data['avistamientos_json'] = json_encode($avistamientos_mapa);
    }

    // --- LÓGICA PARA EL MAPA DE REPORTES DE PERDIDOS ---
    $sql_perdidos = "SELECT r.latitud, r.longitud, a.nombre, a.imagen_url, p.titulo, p.id_publicacion
                     FROM reportes_perdidos r
                     JOIN animales a ON r.id_animal = a.id_animal
                     JOIN publicaciones p ON a.id_animal = p.id_animal
                     WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL AND a.estado = 'Perdido'
                     ORDER BY p.fecha_publicacion DESC
                     LIMIT 50";

    if ($result_perdidos = $conexion->query($sql_perdidos)) {
        $perdidos_mapa = [];
        while ($row = $result_perdidos->fetch_assoc()) {
            $row['popup_html'] = "
                <div style='text-align:center;'>
                    <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Foto de " . htmlspecialchars($row['nombre']) . "' style='width:150px; height:auto; border-radius:4px;'>
                    <p><strong>¡SE BUSCA!</strong><br>" . htmlspecialchars($row['titulo']) . "</p>
                </div>";
            $perdidos_mapa[] = $row;
        }
        $map_data['perdidos_json'] = json_encode($perdidos_mapa);
    }

    // --- LÓGICA PARA EL MAPA DE PUBLICACIONES (ADOPCIÓN, HOGAR TEMPORAL, REFUGIOS) ---
    // Esta parte se mantiene en index.php ya que depende de los resultados de la paginación principal.

    return $map_data;
}

/**
 * Prepara los datos de las publicaciones para ser mostrados en el mapa.
 *
 * @param array $animales Array de animales con sus datos.
 * @return string JSON con los datos de los marcadores de publicaciones.
 */
function mapa_publicaciones(array $animales): string
{
    $publicaciones_mapa = [];
    foreach ($animales as $animal) {
        if (isset($animal['latitud']) && isset($animal['longitud'])) {
            // No incluimos 'Perdido' (ya tiene su propia consulta) ni los que ya no están disponibles.
            $estados_excluidos = ['Perdido', 'Adoptado', 'Encontrado'];
            if (!in_array($animal['estado'], $estados_excluidos)) {
                $popup_html = "
                    <div style='text-align:center;'>
                        <img src='" . $animal['imagen'] . "' alt='Foto de " . $animal['nombre'] . "' style='width:150px; height:auto; border-radius:4px;'>
                        <p><strong>" . $animal['titulo'] . "</strong><br>(" . $animal['estado'] . ")</p>
                    </div>";
                
                $publicaciones_mapa[] = [ 'latitud' => $animal['latitud'], 'longitud' => $animal['longitud'], 'popup_html' => $popup_html, 'es_refugio' => $animal['es_refugio'] ];
            }
        }
    }
    return json_encode($publicaciones_mapa);
}

?>
