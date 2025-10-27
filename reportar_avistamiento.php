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

// Obtener nombre del animal para mostrarlo
$sql_animal = "SELECT nombre FROM animales WHERE id_animal = ?";
$nombre_animal = '';
if ($stmt = $conexion->prepare($sql_animal)) {
    $stmt->bind_param("i", $id_animal);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nombre_animal = $row['nombre'];
    } else {
        die("Animal no encontrado.");
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportar Avistamiento de <?php echo htmlspecialchars($nombre_animal); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Reportar Avistamiento de "<?php echo htmlspecialchars($nombre_animal); ?>"</h2>
        <p>Si crees que has visto a esta mascota, por favor, completa los siguientes datos para ayudar a su dueño.</p>
        <form action="procesar_avistamiento.php" method="post">
            <input type="hidden" name="id_animal" value="<?php echo $id_animal; ?>">
            <div class="form-group">
                <label>Última ubicación donde fue visto</label>
                <input type="text" name="ultima_ubicacion_vista" placeholder="Ej: Cerca del parque central, Calle Falsa 123" required>
            </div>
            <div class="form-group">
                <label>Características distintivas o estado del animal (opcional)</label>
                <textarea name="caracteristicas_distintivas" rows="4" placeholder="Ej: Llevaba un collar rojo, parecía asustado, cojeaba un poco..."></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Enviar Reporte de Avistamiento">
            </div>
            <a href="index.php">Cancelar y volver</a>
        </form>
    </div>
</body>
</html>