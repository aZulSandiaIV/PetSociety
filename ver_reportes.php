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

// --- Obtener los reportes de avistamiento para este animal ---
$sql = "SELECT r.ultima_ubicacion_vista, r.caracteristicas_distintivas, r.fecha_reporte, u.nombre AS nombre_reportador
        FROM reportes_perdidos r
        JOIN usuarios u ON r.id_usuario_reportador = u.id_usuario
        WHERE r.id_animal = ?
        ORDER BY r.fecha_reporte DESC";

$reportes = [];
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $id_animal);
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
    <title>Reportes de Avistamiento - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .reporte-card { background: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .reporte-header { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .reporte-header span { color: #777; font-size: 0.9em; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1><a href="index.php">PetSociety</a></h1></div>
            <nav><ul><li><a href="mis_publicaciones.php">Mis Publicaciones</a></li><li><a href="logout.php">Cerrar Sesión</a></li></ul></nav>
        </div>
    </header>

    <div class="container">
        <h2>Reportes de Avistamiento</h2>
        <?php if (!empty($reportes)): ?>
            <?php foreach ($reportes as $reporte): ?>
                <div class="reporte-card">
                    <div class="reporte-header">
                        <strong>Visto por:</strong> <?php echo htmlspecialchars($reporte['nombre_reportador']); ?><br>
                        <span><strong>Fecha del reporte:</strong> <?php echo date("d/m/Y H:i", strtotime($reporte['fecha_reporte'])); ?></span>
                    </div>
                    <p><strong>Ubicación del avistamiento:</strong> <?php echo htmlspecialchars($reporte['ultima_ubicacion_vista']); ?></p>
                    <?php if (!empty($reporte['caracteristicas_distintivas'])): ?>
                        <p><strong>Notas adicionales:</strong> <?php echo nl2br(htmlspecialchars($reporte['caracteristicas_distintivas'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aún no hay reportes de avistamiento para esta mascota.</p>
        <?php endif; ?>
    </div>
</body>
</html>