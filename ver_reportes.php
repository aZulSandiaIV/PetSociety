<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

if (!isset($_GET['id_animal']) || empty($_GET['id_animal'])) {
    die("Error: No se especificó un animal.");
}
$id_animal = intval($_GET['id_animal']);
$id_usuario_actual = $_SESSION['id_usuario'];

// --- Comprobación de seguridad: Verificar que el usuario es el dueño de la publicación ---
$sql_check_owner = "SELECT p.id_publicacion FROM publicaciones p WHERE p.id_animal = ? AND p.id_usuario_publicador = ?";
$es_dueño = false;
if ($stmt_check = $conexion->prepare($sql_check_owner)) {
    $stmt_check->bind_param("ii", $id_animal, $id_usuario_actual);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $es_dueño = true;
    }
    $stmt_check->close();
}
if (!$es_dueño) {
    die("Acceso denegado. No tienes permiso para ver los reportes de esta mascota.");
}

// --- Obtener los reportes de avistamiento para este animal ---
$sql = "SELECT r.id_usuario_reportador, r.ultima_ubicacion_vista, r.caracteristicas_distintivas, r.fecha_reporte, r.latitud, r.longitud, u.nombre AS nombre_reportador
        FROM reportes_perdidos r
        JOIN usuarios u ON r.id_usuario_reportador = u.id_usuario
        WHERE r.id_animal = ? AND r.id_usuario_reportador != ?
        ORDER BY r.fecha_reporte DESC";

$reportes = [];
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("ii", $id_animal, $id_usuario_actual);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reportes[] = $row;
    }
    $stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Avistamiento - PetSociety</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <style>
        .reporte-card { background: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .reporte-header { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .reporte-header span { color: #777; font-size: 0.9em; }
        .reporte-mapa { height: 250px; margin-top: 15px; border-radius: 8px; z-index: 1; }
        .reporte-actions { margin-top: 15px; }
        .back-button { display: inline-block; margin-bottom: 20px; background-color: #6c757d; }
    </style>
</head>
<body>
    <?php include 'header_estandar.php'; ?>

    <div class="container">
        <h2>Reportes de Avistamiento</h2>
        <a href="mi_perfil.php" class="btn back-button">Volver a Mi Perfil</a>
        <?php if (!empty($reportes)): ?>
            <?php foreach ($reportes as $index => $reporte): ?>
                <div class="reporte-card" id="reporte-<?php echo $index; ?>">
                    <div class="reporte-header">
                        <strong>Visto por:</strong> <?php echo htmlspecialchars($reporte['nombre_reportador']); ?><br>
                        <span><strong>Fecha del reporte:</strong> <?php echo date("d/m/Y H:i", strtotime($reporte['fecha_reporte'])); ?></span>
                    </div>
                    <p><strong>Ubicación del avistamiento:</strong> <?php echo htmlspecialchars($reporte['ultima_ubicacion_vista']); ?></p>
                    <?php if (!empty($reporte['caracteristicas_distintivas'])): ?>
                        <p><strong>Notas adicionales:</strong> <?php echo nl2br(htmlspecialchars($reporte['caracteristicas_distintivas'])); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($reporte['latitud']) && !empty($reporte['longitud'])): ?>
                        <div id="mapa-<?php echo $index; ?>" class="reporte-mapa"></div>
                    <?php endif; ?>

                    <div class="reporte-actions">
                        <a href="enviar_mensaje.php?id_destinatario=<?php echo $reporte['id_usuario_reportador']; ?>" class="btn">Contactar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aún no hay reportes de avistamiento para esta mascota.</p>
        <?php endif; ?>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="funciones_js.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activar la interactividad de los menús
            interactividad_menus();

            // Inicializar los mapas para cada reporte
            <?php foreach ($reportes as $index => $reporte): ?>
                <?php if (!empty($reporte['latitud']) && !empty($reporte['longitud'])): ?>
                    (function() {
                        const lat = <?php echo $reporte['latitud']; ?>;
                        const lon = <?php echo $reporte['longitud']; ?>;
                        const mapId = 'mapa-<?php echo $index; ?>';
                        
                        if (document.getElementById(mapId)) {
                            const mapa = L.map(mapId).setView([lat, lon], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                            }).addTo(mapa);
                            
                            L.marker([lat, lon]).addTo(mapa)
                                .bindPopup('Avistamiento reportado aquí.')
                                .openPopup();
                        }
                    })();
                <?php endif; ?>
            <?php endforeach; ?>
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>