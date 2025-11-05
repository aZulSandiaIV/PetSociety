<?php
include 'admin_header.php';

// Consultas para estadísticas
$total_usuarios = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$total_refugios = $conexion->query("SELECT COUNT(*) AS total FROM usuarios WHERE es_refugio = 1")->fetch_assoc()['total'];
$total_publicaciones = $conexion->query("SELECT COUNT(*) AS total FROM publicaciones")->fetch_assoc()['total'];
$total_animales = $conexion->query("SELECT COUNT(*) AS total FROM animales")->fetch_assoc()['total'];

// Estadísticas por estado de animales
$animales_por_estado = $conexion->query("SELECT estado, COUNT(*) as total FROM animales GROUP BY estado");

// Estadísticas por especie
$animales_por_especie = $conexion->query("SELECT especie, COUNT(*) as total FROM animales GROUP BY especie");

// Publicaciones recientes (últimos 30 días)
$publicaciones_recientes = $conexion->query("SELECT COUNT(*) AS total FROM publicaciones WHERE fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['total'];

// Adopciones realizadas
$total_adopciones = $conexion->query("SELECT COUNT(*) AS total FROM adopciones")->fetch_assoc()['total'];
?>

<div class="admin-cards">
    <div class="admin-card">
        <h3>👥 Usuarios Registrados</h3>
        <div class="card-number"><?php echo $total_usuarios; ?></div>
        <p>Total de usuarios en la plataforma</p>
    </div>
    
    <div class="admin-card">
        <h3>🏠 Refugios</h3>
        <div class="card-number"><?php echo $total_refugios; ?></div>
        <p>Refugios registrados activos</p>
    </div>
    
    <div class="admin-card">
        <h3>📝 Publicaciones</h3>
        <div class="card-number"><?php echo $total_publicaciones; ?></div>
        <p>Total de publicaciones históricas</p>
    </div>
    
    <div class="admin-card">
        <h3>🐾 Animales</h3>
        <div class="card-number"><?php echo $total_animales; ?></div>
        <p>Animales registrados en el sistema</p>
    </div>
    
    <div class="admin-card">
        <h3>💚 Adopciones</h3>
        <div class="card-number"><?php echo $total_adopciones; ?></div>
        <p>Adopciones realizadas exitosamente</p>
    </div>
    
    <div class="admin-card">
        <h3>📈 Publicaciones Recientes</h3>
        <div class="card-number"><?php echo $publicaciones_recientes; ?></div>
        <p>Publicaciones en los últimos 30 días</p>
    </div>
</div>

<div class="admin-table-container">
    <h3>Animales por Estado</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $animales_por_estado->data_seek(0); // Reset del resultado
            while($row = $animales_por_estado->fetch_assoc()): 
                $porcentaje = $total_animales > 0 ? round(($row['total'] / $total_animales) * 100, 1) : 0;
                $status_class = '';
                if (strtolower($row['estado']) == 'en adopción') {
                    $status_class = 'status-adopcion';
                } elseif (strtolower($row['estado']) == 'perdido') {
                    $status_class = 'status-perdido';
                } elseif (strtolower($row['estado']) == 'encontrado') {
                    $status_class = 'status-encontrado';
                } elseif (strtolower($row['estado']) == 'hogar temporal') {
                    $status_class = 'status-hogar-temporal';
                } else {
                    $status_class = 'status-adopcion';
                }
            ?>
                <tr>
                    <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['estado']); ?></span></td>
                    <td><?php echo $row['total']; ?></td>
                    <td><?php echo $porcentaje; ?>%</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="admin-table-container">
    <h3>Animales por Especie</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Especie</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $animales_por_especie->data_seek(0); // Reset del resultado
            while($row = $animales_por_especie->fetch_assoc()): 
                $porcentaje = $total_animales > 0 ? round(($row['total'] / $total_animales) * 100, 1) : 0;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['especie']); ?></td>
                    <td><?php echo $row['total']; ?></td>
                    <td><?php echo $porcentaje; ?>%</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>