<?php
include 'admin_header.php';

// Lógica para eliminar una publicación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_publicacion_a_borrar = intval($_POST['delete_id']);

    // Primero, obtenemos el id_animal asociado a la publicación.
    $sql_get_animal = "SELECT id_animal FROM publicaciones WHERE id_publicacion = ?";
    if ($stmt_get = $conexion->prepare($sql_get_animal)) {
        $stmt_get->bind_param("i", $id_publicacion_a_borrar);
        $stmt_get->execute();
        $result_get = $stmt_get->get_result();

        if ($row_get = $result_get->fetch_assoc()) {
            $id_animal_a_borrar = $row_get['id_animal'];

            // Intentamos borrar el animal. La publicación se borrará en cascada.
            $sql_delete = "DELETE FROM animales WHERE id_animal = ?";
            if ($stmt_del = $conexion->prepare($sql_delete)) {
                $stmt_del->bind_param("i", $id_animal_a_borrar);
                try {
                    $stmt_del->execute();
                    echo "<div class='admin-alert success'>✅ Publicación eliminada correctamente.</div>";
                } catch (mysqli_sql_exception $e) {
                    // Capturamos el error de Foreign Key (código 1451)
                    if ($e->getCode() == 1451) {
                        echo "<div class='admin-alert warning'>⚠️ Error: No se puede eliminar un registro de animal ya adoptado.</div>";
                    } else {
                        // Otro tipo de error de base de datos
                        echo "<div class='admin-alert error'>❌ Error al eliminar la publicación: " . $e->getMessage() . "</div>";
                    }
                }
            }
        }
    }
}

// Consulta para obtener todas las publicaciones con información adicional
$sql = "SELECT p.id_publicacion, p.titulo, p.fecha_publicacion, p.tipo_publicacion,
               u.nombre AS autor, u.es_refugio,
               a.nombre AS animal, a.especie, a.estado
        FROM publicaciones p 
        JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario 
        JOIN animales a ON p.id_animal = a.id_animal 
        ORDER BY p.fecha_publicacion DESC";
$result = $conexion->query($sql);
$total_publicaciones = $result->num_rows;
?>

<div class="admin-cards">
    <div class="admin-card">
        <h3>📝 Total de Publicaciones</h3>
        <div class="card-number"><?php echo $total_publicaciones; ?></div>
        <p>Publicaciones registradas en el sistema</p>
    </div>
</div>

<div class="admin-table-container">
    <h3>Lista de Publicaciones</h3>
    <?php if ($total_publicaciones > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Animal</th>
                    <th>Especie</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Autor</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $result->data_seek(0); // Reset del resultado
                while ($row = $result->fetch_assoc()): 
                    // Determinar clase CSS para el tipo de publicación
                    $tipo_class = '';
                    if (strtolower($row['tipo_publicacion']) == 'adopción') {
                        $tipo_class = 'status-adopcion';
                    } elseif (strtolower($row['tipo_publicacion']) == 'perdido') {
                        $tipo_class = 'status-perdido';
                    } elseif (strtolower($row['tipo_publicacion']) == 'encontrado') {
                        $tipo_class = 'status-encontrado';
                    } elseif (strtolower($row['tipo_publicacion']) == 'hogar temporal') {
                        $tipo_class = 'status-hogar-temporal';
                    } else {
                        $tipo_class = 'status-adopcion';
                    }
                    
                    // Determinar clase CSS para el estado del animal
                    $estado_class = '';
                    if (strtolower($row['estado']) == 'en adopción') {
                        $estado_class = 'status-adopcion';
                    } elseif (strtolower($row['estado']) == 'perdido') {
                        $estado_class = 'status-perdido';
                    } elseif (strtolower($row['estado']) == 'encontrado') {
                        $estado_class = 'status-encontrado';
                    } elseif (strtolower($row['estado']) == 'adoptado') {
                        $estado_class = 'status-adopcion';
                    } elseif (strtolower($row['estado']) == 'hogar temporal') {
                        $estado_class = 'status-hogar-temporal';
                    } else {
                        $estado_class = 'status-adopcion';
                    }
                ?>
                    <tr>
                        <td class="title-cell"><?php echo htmlspecialchars($row['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($row['animal']); ?></td>
                        <td><?php echo htmlspecialchars($row['especie']); ?></td>
                        <td><span class="status-badge <?php echo $tipo_class; ?>"><?php echo htmlspecialchars($row['tipo_publicacion']); ?></span></td>
                        <td><span class="status-badge <?php echo $estado_class; ?>"><?php echo htmlspecialchars($row['estado']); ?></span></td>
                        <td>
                            <?php echo htmlspecialchars($row['autor']); ?>
                            <?php if ($row['es_refugio']): ?>
                                <small class="refugio-indicator">🏠 Refugio</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_publicacion'])); ?></td>
                        <td>
                            <form method="post" class="admin-inline-form" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta publicación?\n\nTítulo: <?php echo htmlspecialchars($row['titulo']); ?>\nAnimal: <?php echo htmlspecialchars($row['animal']); ?>\n\nEsta acción es irreversible.');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id_publicacion']; ?>">
                                <button type="submit" class="admin-btn admin-btn-danger admin-btn-small">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="admin-empty">
            <p>No hay publicaciones registradas en el sistema.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>