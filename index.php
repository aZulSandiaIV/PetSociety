<?php
session_start();
require_once "config.php";
?>

<?php
// --- LÓGICA ---
// 1. Recoger todos los posibles filtros desde GET. Ahora los filtros pueden ser arrays.
$filtros = [
    'estado' => $_GET['estado'] ?? [],
    'especie' => $_GET['especie'] ?? [],
    'q' => $_GET['q'] ?? '', // Búsqueda por texto (zona, etc.)
    'tamaño' => $_GET['tamaño'] ?? [],
    'edad' => $_GET['filtro_edad'] ?? '',
    'color' => $_GET['filtro_color'] ?? '',
    'fecha' => $_GET['filtro_fecha'] ?? ''
];

$where_conditions = [];
$params = [];
$types = '';

// 2. Construir la cláusula WHERE dinámicamente
// Filtro de estado
if (!empty($filtros['estado'])) {
    $placeholders = implode(',', array_fill(0, count($filtros['estado']), '?'));
    $where_conditions[] = "a.estado IN ($placeholders)";
    $params = array_merge($params, $filtros['estado']);
    $types .= str_repeat('s', count($filtros['estado']));
}

// Filtro de especie
if (!empty($filtros['especie'])) {
    $placeholders = implode(',', array_fill(0, count($filtros['especie']), '?'));
    $where_conditions[] = "a.especie IN ($placeholders)";
    $params = array_merge($params, $filtros['especie']);
    $types .= str_repeat('s', count($filtros['especie']));
}

// Búsqueda por texto (zona, título, descripción)
if (!empty($filtros['q'])) {
    $where_conditions[] = "(LOWER(p.titulo) LIKE ? OR LOWER(p.contenido) LIKE ? OR LOWER(p.ubicacion_texto) LIKE ?)";
    $query_param = "%" . strtolower($filtros['q']) . "%";
    array_push($params, $query_param, $query_param, $query_param);
    $types .= 'sss';
}

// Filtros de animal
if (!empty($filtros['tamaño'])) {
    $placeholders = implode(',', array_fill(0, count($filtros['tamaño']), '?'));
    $where_conditions[] = "a.tamaño IN ($placeholders)";
    $params = array_merge($params, $filtros['tamaño']);
    $types .= str_repeat('s', count($filtros['tamaño']));
}
if (!empty($filtros['edad'])) { $where_conditions[] = "a.edad LIKE ?"; $params[] = "%" . $filtros['edad'] . "%"; $types .= 's'; }
if (!empty($filtros['color'])) { $where_conditions[] = "a.color LIKE ?"; $params[] = "%" . $filtros['color'] . "%"; $types .= 's'; }

// Filtro de fecha
if (!empty($filtros['fecha'])) {
    $where_conditions[] = "p.fecha_publicacion >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = intval($filtros['fecha']);
    $types .= 'i';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// 3. Preparar la consulta para obtener las publicaciones
$sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado, a.tamaño, a.edad, a.color,
               p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido, p.latitud, p.longitud,
               u.es_refugio
        FROM publicaciones p 
        JOIN animales a ON p.id_animal = a.id_animal 
        JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario
        $where_clause
        ORDER BY p.fecha_publicacion DESC";

// 4. Ejecutar la consulta con sentencias preparadas y procesar los resultados
$animales = [];
if ($stmt = $conexion->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Preparamos los datos para la vista
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
                'contenido_corto' => nl2br(htmlspecialchars(substr($row['contenido'], 0, 100)))
            ];
            // Añadir latitud y longitud si existen
            if (!empty($row['latitud']) && !empty($row['longitud'])) {
                $animales[count($animales)-1]['latitud'] = $row['latitud'];
                $animales[count($animales)-1]['longitud'] = $row['longitud'];
            }
        }
    }
    $stmt->close();
}

// --- LÓGICA PARA EL MAPA DE AVISTAMIENTOS ---
$sql_avistamientos = "SELECT id_avistamiento, latitud, longitud, imagen_url, descripcion, fecha_avistamiento 
                      FROM avistamientos 
                      WHERE estado = 'Visto'
                      ORDER BY fecha_avistamiento DESC
                      LIMIT 50";

$avistamientos_json = "[]";
if ($result_avistamientos = $conexion->query($sql_avistamientos)) {
    $avistamientos_mapa = [];
    while ($row = $result_avistamientos->fetch_assoc()) {
        // Preparamos los datos para el popup del mapa
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
    $avistamientos_json = json_encode($avistamientos_mapa);
}

// --- LÓGICA PARA EL MAPA DE REPORTES DE PERDIDOS ---
$sql_perdidos = "SELECT 
                    r.latitud, r.longitud,
                    a.nombre, a.imagen_url,
                    p.titulo, p.id_publicacion
                 FROM reportes_perdidos r
                 JOIN animales a ON r.id_animal = a.id_animal
                 JOIN publicaciones p ON a.id_animal = p.id_animal
                 WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL AND a.estado = 'Perdido'
                 ORDER BY p.fecha_publicacion DESC
                 LIMIT 50";

$perdidos_json = "[]";
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
    $perdidos_json = json_encode($perdidos_mapa);
}

// --- LÓGICA PARA EL MAPA DE PUBLICACIONES (ADOPCIÓN, HOGAR TEMPORAL, REFUGIOS) ---
$publicaciones_mapa = [];
foreach ($animales as $animal) {
    if (isset($animal['latitud']) && isset($animal['longitud'])) {
        // No incluimos los 'Perdido' porque ya se obtienen en su propia consulta
        if ($animal['estado'] != 'Perdido') {
            $popup_html = "
                <div style='text-align:center;'>
                    <img src='" . $animal['imagen'] . "' alt='Foto de " . $animal['nombre'] . "' style='width:150px; height:auto; border-radius:4px;'>
                    <p><strong>" . $animal['titulo'] . "</strong><br>(" . $animal['estado'] . ")</p>
                </div>";
            
            $publicaciones_mapa[] = [
                'latitud' => $animal['latitud'],
                'longitud' => $animal['longitud'],
                'popup_html' => $popup_html,
                'es_refugio' => $animal['es_refugio']
            ];
        }
    }
}
$publicaciones_json = json_encode($publicaciones_mapa);


$conexion->close();

// 3. Incluir la vista
// El archivo de la vista ahora tendrá acceso a la variable $animales
require 'index.view.php';

?>