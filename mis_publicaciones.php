<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";
$id_usuario = $_SESSION['id_usuario'];

// --- Lógica para cambiar estado a "Encontrado" ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['marcar_encontrado'])) {
    $id_animal_encontrado = intval($_POST['id_animal']);
    $sql_update = "UPDATE animales SET estado = 'Encontrado' WHERE id_animal = ? AND id_animal IN (SELECT id_animal FROM publicaciones WHERE id_usuario_publicador = ?)";
    if ($stmt_update = $conexion->prepare($sql_update)) {
        $stmt_update->bind_param("ii", $id_animal_encontrado, $id_usuario);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

// --- Obtener las publicaciones del usuario ---
$sql = "SELECT p.id_publicacion, p.titulo, p.tipo_publicacion, a.id_animal, a.nombre, a.estado
        FROM publicaciones p
        JOIN animales a ON p.id_animal = a.id_animal
        WHERE p.id_usuario_publicador = ?
        ORDER BY p.fecha_publicacion DESC";

$publicaciones = [];
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $publicaciones[] = $row;
    }
    $stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Publicaciones - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .pub-card { background: #fff; padding: 20px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .pub-info h3 { margin: 0 0 10px; color: #2C5F2D; }
        .pub-info span { background: #eee; padding: 3px 8px; border-radius: 10px; font-size: 0.8em; }
        .pub-actions .btn { width: auto; margin-left: 10px; }
        .estado-adoptado { background-color: #97BC62 !important; color: white; }
        .estado-encontrado { background-color: #97BC62 !important; color: white; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1><a href="index.php">PetSociety</a></h1></div>
            <nav><ul><li><a href="index.php">Inicio</a></li><li><a href="logout.php">Cerrar Sesión</a></li></ul></nav>
        </div>
    </header>

    <div class="container">
        <h2>Gestionar Mis Publicaciones</h2>
        <?php if (!empty($publicaciones)): ?>
            <?php foreach ($publicaciones as $pub): ?>
                <div class="pub-card">
                    <div class="pub-info">
                        <h3><?php echo htmlspecialchars($pub['titulo']); ?> (<?php echo htmlspecialchars($pub['nombre']); ?>)</h3>
                        <span class="pub-estado <?php if($pub['estado'] == 'Adoptado' || $pub['estado'] == 'Encontrado') echo 'estado-'.$pub['estado']; ?>">
                            Estado: <?php echo htmlspecialchars($pub['estado']); ?>
                        </span>
                    </div>
                    <div class="pub-actions">
                        <?php if ($pub['estado'] == 'En Adopción' || $pub['estado'] == 'Hogar Temporal'): ?>
                            <a href="gestionar_adopcion.php?id_pub=<?php echo $pub['id_publicacion']; ?>&id_animal=<?php echo $pub['id_animal']; ?>" class="btn">Gestionar Adopción</a>
                        <?php elseif ($pub['estado'] == 'Perdido'): ?>
                            <form method="post" style="display: inline;">
                                <a href="ver_reportes.php?id_animal=<?php echo $pub['id_animal']; ?>" class="btn" style="background-color: #97BC62;">Ver Avistamientos</a>
                                <input type="hidden" name="id_animal" value="<?php echo $pub['id_animal']; ?>">
                                <button type="submit" name="marcar_encontrado" class="btn" onclick="return confirm('¿Estás seguro de que has encontrado a tu mascota?');">Marcar como Encontrado</button>
                            </form>
                        <?php else: ?>
                            <span>Gestión finalizada</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No has realizado ninguna publicación todavía. <a href="publicar.php">¡Crea una ahora!</a></p>
        <?php endif; ?>
    </div>
</body>
</html>