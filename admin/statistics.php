<?php
include 'admin_header.php'; // Esta ruta ahora es correcta

// Consultas para estadísticas
$total_usuarios = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$total_refugios = $conexion->query("SELECT COUNT(*) AS total FROM usuarios WHERE es_refugio = 1")->fetch_assoc()['total'];
$total_publicaciones = $conexion->query("SELECT COUNT(*) AS total FROM publicaciones")->fetch_assoc()['total'];
$total_animales = $conexion->query("SELECT COUNT(*) AS total FROM animales")->fetch_assoc()['total'];
$animales_por_especie = $conexion->query("SELECT especie, COUNT(*) as total FROM animales GROUP BY especie");
?>
<title>Estadísticas - Admin</title>
<h2>Estadísticas del Sitio</h2>
<div class="form-container" style="max-width: none;">
    <h3>Resumen General</h3>
    <ul>
        <li><strong>Total de Usuarios Registrados:</strong> <?php echo $total_usuarios; ?></li>
        <li><strong>Total de Refugios:</strong> <?php echo $total_refugios; ?></li>
        <li><strong>Total de Publicaciones Históricas:</strong> <?php echo $total_publicaciones; ?></li>
        <li><strong>Total de Animales Registrados:</strong> <?php echo $total_animales; ?></li>
    </ul>
    <h3>Animales por Especie</h3>
    <ul>
    <?php while($row = $animales_por_especie->fetch_assoc()): ?>
        <li><strong><?php echo htmlspecialchars($row['especie']); ?>:</strong> <?php echo $row['total']; ?></li>
    <?php endwhile; ?>
    </ul>
</div>

<?php include 'admin_footer.php'; ?> <!-- Esta ruta ahora es correcta -->