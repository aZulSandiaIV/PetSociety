<?php
session_start();
require_once "config.php";

// --- CONFIGURACIÓN DE FILTROS PERMITIDOS ---
$allowed_status_filters = ['Adopción', 'Hogar Temporal', 'Perdido'];
$allowed_races_filters = ['Perro', 'Gato', 'Otro'];
$allowed_sizes_filters = ['Pequeño', 'Mediano', 'Grande'];

// --- CONSTRUCCIÓN DE LA CONSULTA SQL ---
$where_clauses = [];

// 1. **Filtro base**: Excluir siempre los animales adoptados o encontrados.
$where_clauses[] = "a.estado NOT IN ('Adoptado', 'Encontrado')";

// 2. Filtro por estado de la publicación (Adopción, Hogar Temporal, Perdido)
if (isset($_GET['status']) && in_array($_GET['status'], $allowed_status_filters, true)) {
    $filtro_estado = $conexion->real_escape_string($_GET['status']);
    $where_clauses[] = "p.tipo_publicacion = '{$filtro_estado}'";
}

// 3. Filtro por especie (race en el frontend)
if (isset($_GET['race']) && in_array($_GET['race'], $allowed_races_filters, true)) {
    $filtro_especie = $conexion->real_escape_string($_GET['race']);
    $where_clauses[] = "a.especie = '{$filtro_especie}'";
}

// 4. Filtro por tamaño (size)
if (isset($_GET['size']) && in_array($_GET['size'], $allowed_sizes_filters, true)) {
    $filtro_tamano = $conexion->real_escape_string($_GET['size']);
    $where_clauses[] = "a.tamaño = '{$filtro_tamano}'";
}

// 5. Filtro por búsqueda de texto
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conexion->real_escape_string($_GET['search']);
    $where_clauses[] = "(p.titulo LIKE '%{$search_term}%' OR p.contenido LIKE '%{$search_term}%' OR a.nombre LIKE '%{$search_term}%' OR a.raza LIKE '%{$search_term}%')";
}

// --- PAGINACIÓN ---
$cargar_apartir = isset($_GET['cargar_apartir']) ? (int)$_GET['cargar_apartir'] : 0;
$cargar_cantidad = isset($_GET['cargar_cantidad']) ? (int)$_GET['cargar_cantidad'] : 5;
$limit_clause = "LIMIT {$cargar_apartir}, {$cargar_cantidad}";

// --- CONSTRUCCIÓN FINAL DE LA CONSULTA ---
$sql = "SELECT a.id_animal, a.nombre, a.especie, a.raza, a.imagen_url, a.estado, a.tamaño, a.edad, a.color, a.genero,
               p.id_publicacion, p.id_usuario_publicador, p.titulo, p.contenido,
               u.es_refugio
        FROM publicaciones p 
        JOIN animales a ON p.id_animal = a.id_animal 
        JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY p.fecha_publicacion DESC " . $limit_clause;

// --- EJECUCIÓN Y RESPUESTA ---
$animales = [];
$result = $conexion->query($sql);

if ($result === false) {
    http_response_code(500);
    error_log("SQL error en solicitar_publicaciones.php: " . $conexion->error);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Database error']);
    exit;
}

if ($result->num_rows === 0) {
    http_response_code(204); // No Content
    $result->free();
    exit;
}

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
        'descripcion' => nl2br(htmlspecialchars($row['contenido'])) // Descripción completa para el modal
    ];
}

$result->free();
$conexion->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($animales);
?>