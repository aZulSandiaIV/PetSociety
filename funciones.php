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

function mapa_avistamientos(mysqli $conexion, array $animales, string $redirect_page = 'index.php'): array
{
    $map_data = [
        'avistamientos_json' => '[]',
        'perdidos_json' => '[]',
        'publicaciones_json' => '[]'
    ];

    // --- LÓGICA PARA EL MAPA DE AVISTAMIENTOS ---
    $sql_avistamientos = "SELECT id_avistamiento, latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
                          FROM avistamientos 
                          WHERE estado != 'Ya no está'
                          ORDER BY fecha_avistamiento DESC
                          LIMIT 50";

    if ($result_avistamientos = $conexion->query($sql_avistamientos)) {
        $avistamientos_mapa = [];
        while ($row = $result_avistamientos->fetch_assoc()) {
            $botones_avistamiento = '';
            if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
                $botones_avistamiento = "
                    <div style='margin-top:10px; display:flex; justify-content:space-around;'>
                        <a href='actualizar_avistamiento.php?id={$row['id_avistamiento']}&accion=sigue_aqui&redirect={$redirect_page}' style='font-size:0.8em; padding: 4px 8px; background-color:#97BC62; color:white; text-decoration:none; border-radius:4px;'>Sigue Aquí</a>
                        <a href='actualizar_avistamiento.php?id={$row['id_avistamiento']}&accion=no_esta&redirect={$redirect_page}' style='font-size:0.8em; padding: 4px 8px; background-color:#E57373; color:white; text-decoration:none; border-radius:4px;'>Ya no está</a>
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
    $sql_perdidos = "SELECT r.latitud, r.longitud, a.id_animal, a.nombre, a.imagen_url, p.titulo, p.id_publicacion, p.id_usuario_publicador
                     FROM reportes_perdidos r
                     JOIN animales a ON r.id_animal = a.id_animal
                     JOIN publicaciones p ON a.id_animal = p.id_animal
                     WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL AND a.estado = 'Perdido'
                     ORDER BY a.id_animal, r.fecha_reporte DESC";

    if ($result_perdidos = $conexion->query($sql_perdidos)) {
        $id_usuario_actual = $_SESSION['id_usuario'] ?? null;
        $reportes_por_animal = [];

        // Agrupar todos los reportes por animal
        while ($row = $result_perdidos->fetch_assoc()) {
            $reportes_por_animal[$row['id_animal']][] = $row;
        }

        $perdidos_mapa = [];
        foreach ($reportes_por_animal as $id_animal => $reportes) {
            $es_dueño = ($id_usuario_actual && $reportes[0]['id_usuario_publicador'] == $id_usuario_actual);

            if ($es_dueño) {
                // Si es el dueño, añadir todos los reportes para el seguimiento
                foreach ($reportes as $reporte) {
                    $reporte['popup_html'] = "<div style='text-align:center;'><img src='" . htmlspecialchars($reporte['imagen_url']) . "' alt='Foto de " . htmlspecialchars($reporte['nombre']) . "' style='width:150px; height:auto; border-radius:4px;'><p><strong>¡AVISTAMIENTO!</strong><br>" . htmlspecialchars($reporte['titulo']) . "</p></div>";
                    $perdidos_mapa[] = $reporte;
                }
            } else {
                // Si no es el dueño, añadir solo el último reporte (el primero del array ordenado)
                $ultimo_reporte = $reportes[0];
                $ultimo_reporte['popup_html'] = "
                <div style='text-align:center;'>
                    <img src='" . htmlspecialchars($ultimo_reporte['imagen_url']) . "' alt='Foto de " . htmlspecialchars($ultimo_reporte['nombre']) . "' style='width:150px; height:auto; border-radius:4px;'>
                    <p><strong>¡SE BUSCA!</strong><br>Última vez visto cerca de aquí.</p>
                </div>";
                $perdidos_mapa[] = $ultimo_reporte;
            }
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


/**
 * Obtiene las publicaciones de la base de datos aplicando filtros.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @param array $filtros Array con los filtros a aplicar (ej: ['status' => 'Adopción']).
 * @return array Array con los datos de las publicaciones.
 */

function obtener_publicaciones(mysqli $conexion, array $filtros = []): array
{
    // --- CONFIGURACIÓN DE FILTROS PERMITIDOS ---
    $allowed_filters = [
        'status' => ['Adopción', 'Hogar Temporal', 'Perdido'],
        'race'   => ['Perro', 'Gato', 'Otro'],
        'size'   => ['Pequeño', 'Mediano', 'Grande']
    ];

    $where_clauses = ["a.estado NOT IN ('Adoptado', 'Encontrado')"];

    // Filtro por estado
    if (isset($filtros['status']) && in_array($filtros['status'], $allowed_filters['status'], true)) {
        $where_clauses[] = "p.tipo_publicacion = '" . $conexion->real_escape_string($filtros['status']) . "'";
    }

    // Filtro por especie (race)
    if (isset($filtros['race']) && in_array($filtros['race'], $allowed_filters['race'], true)) {
        $where_clauses[] = "a.especie = '" . $conexion->real_escape_string($filtros['race']) . "'";
    }

    // Filtro por tamaño (size)
    if (isset($filtros['size']) && in_array($filtros['size'], $allowed_filters['size'], true)) {
        $where_clauses[] = "a.tamaño = '" . $conexion->real_escape_string($filtros['size']) . "'";
    }

    // Filtro por búsqueda de texto
    if (isset($filtros['search']) && !empty($filtros['search'])) {
        $search_term = $conexion->real_escape_string($filtros['search']);
        $where_clauses[] = "(p.titulo LIKE '%{$search_term}%' OR p.contenido LIKE '%{$search_term}%' OR a.nombre LIKE '%{$search_term}%' OR a.raza LIKE '%{$search_term}%')";
    }

    // Filtro por ID de publicador
    if (isset($filtros['id_publicador']) && !empty($filtros['id_publicador'])) {
        $where_clauses[] = "p.id_usuario_publicador = " . (int)$filtros['id_publicador'];
    }

    // --- PAGINACIÓN ---
    $limit_clause = '';
    if (isset($filtros['cargar_apartir'], $filtros['cargar_cantidad'])) {
        $offset = (int)$filtros['cargar_apartir'];
        $limit = (int)$filtros['cargar_cantidad'];
        $limit_clause = "LIMIT {$offset}, {$limit}";
    }
    
    $sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado, a.tamaño, a.edad, a.color, a.genero,
               p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido, p.latitud, p.longitud,
               u.es_refugio
            FROM publicaciones p 
            JOIN animales a ON p.id_animal = a.id_animal 
            JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario";

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $sql .= " ORDER BY p.fecha_publicacion DESC " . $limit_clause;

    $animales = [];
    $result = $conexion->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $animales[] = [
                'id_animal' => $row['id_animal'],
                'id_publicacion' => $row['id_publicacion'],
                'id_publicador' => $row['id_usuario_publicador'],
                'es_refugio' => $row['es_refugio'],
                'imagen' => $row['imagen_url'] ? htmlspecialchars($row['imagen_url']) : 'https://via.placeholder.com/300x200.png?text=Sin+Foto',
                'nombre' => htmlspecialchars($row['nombre']),
                'titulo' => htmlspecialchars($row['titulo']),
                'estado' => htmlspecialchars($row['estado']),
                'especie' => htmlspecialchars($row['especie']),
                'raza' => htmlspecialchars($row['raza']),
                'tamaño' => htmlspecialchars($row['tamaño'] ?? ''),
                'edad' => htmlspecialchars($row['edad'] ?? ''),
                'color' => htmlspecialchars($row['color'] ?? ''),
                'genero' => htmlspecialchars($row['genero'] ?? ''),
                'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100))),
                'descripcion' => nl2br(htmlspecialchars($row['contenido'])), // Descripción completa para el modal
                'latitud' => $row['latitud'],
                'longitud' => $row['longitud']
            ];
        }
        $result->free();
    }
    return $animales;
}


/**
 * Obtiene el nombre de un animal a partir de su ID.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @param int $id_animal ID del animal.
 * @return string|null El nombre del animal o null si no se encuentra.
 */

function obtener_nombre_animal(mysqli $conexion, int $id_animal): ?string
{
    $sql = "SELECT nombre FROM animales WHERE id_animal = ?";
    $nombre = null;
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $id_animal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $nombre = $row['nombre'];
        }
        $stmt->close();
    }
    return $nombre;
}


/**
 * Obtiene los datos de un refugio específico.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @param int $id_refugio ID del refugio a buscar.
 * @return array|null Array con los datos del refugio o null si no se encuentra o no es un refugio.
 */

function obtener_datos_refugio(mysqli $conexion, int $id_refugio): ?array
{
    $sql = "SELECT nombre, email, telefono, foto_perfil_url FROM usuarios WHERE id_usuario = ? AND es_refugio = 1";
    $refugio = null;
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $id_refugio);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $refugio = $result->fetch_assoc();
        }
        $stmt->close();
    }
    return $refugio;
}


/**
 * Obtiene los últimos 50 avistamientos de la base de datos.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @return string JSON con los datos de los últimos avistamientos.
 */


function obtener_ultimos_avistamientos(mysqli $conexion): string
{
    $sql = "SELECT latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
            FROM avistamientos 
            ORDER BY fecha_avistamiento DESC 
            LIMIT 50";

    $avistamientos = [];
    if ($result = $conexion->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $row['popup_html'] = "
                <div>
                    <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Avistamiento' style='width:150px; height:auto; border-radius:4px;'>
                    <p>" . htmlspecialchars($row['descripcion']) . "</p>
                    <small>Visto el: " . date('d/m/Y H:i', strtotime($row['fecha_avistamiento'])) . "</small>
                </div>";
            $avistamientos[] = $row;
        }
        $result->free();
    }
    return json_encode($avistamientos);
}

/**
 * Obtiene los últimos 50 perdidos de la base de datos.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @return string JSON con los datos de los últimos perdidos.
 */
function obtener_ultimos_perdidos(mysqli $conexion): string
{
    $sql = "SELECT r.latitud, r.longitud, a.nombre, a.imagen_url, p.titulo
            FROM reportes_perdidos r
            JOIN animales a ON r.id_animal = a.id_animal
            JOIN publicaciones p ON a.id_animal = p.id_animal
            WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL AND a.estado = 'Perdido'
            ORDER BY r.fecha_reporte DESC
            LIMIT 50";

    $perdidos = [];
    if ($result = $conexion->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $row['popup_html'] = "
                <div style='text-align:center;'>
                    <img src='" . htmlspecialchars($row['imagen_url']) . "' alt='Foto de " . htmlspecialchars($row['nombre']) . "' style='width:150px; height:auto; border-radius:4px;'>
                    <p><strong>¡SE BUSCA!</strong><br>" . htmlspecialchars($row['titulo']) . "</p>
                    <small>Última vez visto cerca de aquí.</small>
                </div>";
            $perdidos[] = $row;
        }
        $result->free();
    }
    return json_encode($perdidos);
}

/**
 * Obtiene una lista de todos los refugios registrados.
 *
 * @param mysqli $conexion Objeto de conexión a la base de datos.
 * @return array Array con los datos de todos los refugios.
 */

function obtener_refugios(mysqli $conexion): array
{
    // La subconsulta ahora cuenta las publicaciones activas en lugar de concatenar títulos.
    $sql = "SELECT id_usuario, nombre, email, telefono, foto_perfil_url, 
                   (SELECT COUNT(p.id_publicacion) FROM publicaciones p JOIN animales a ON p.id_animal = a.id_animal WHERE p.id_usuario_publicador = u.id_usuario AND a.estado NOT IN ('Adoptado', 'Encontrado')) AS num_publicaciones 
            FROM usuarios u 
            WHERE es_refugio = 1";
    $refugios = [];
    if ($result = $conexion->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $refugios[] = $row;
        }
        $result->free();
    }
    return $refugios;
}
?>
