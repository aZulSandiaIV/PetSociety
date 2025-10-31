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
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <ul>
                    <li>Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                    <?php endif; ?>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="mis_publicaciones.php">Mi Perfil</a></li>
                    <li><a href="buzon.php">Buzón</a></li>
                    <li><a href="publicar.php">Publicar Animal</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

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