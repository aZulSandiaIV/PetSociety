<?php
include 'admin_header.php'; // Esta ruta ahora es correcta

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
                    echo "<div class='alert' style='background-color: #d4edda; color: #155724;'>Publicación eliminada correctamente.</div>";
                } catch (mysqli_sql_exception $e) {
                    // Capturamos el error de Foreign Key (código 1451)
                    if ($e->getCode() == 1451) {
                        echo "<div class='alert'>Error: No se puede eliminar un registro de animal ya adoptado.</div>";
                    } else {
                        // Otro tipo de error de base de datos
                        echo "<div class='alert'>Error al eliminar la publicación: " . $e->getMessage() . "</div>";
                    }
                }
            }
        }
    }
}

$sql = "SELECT p.id_publicacion, p.titulo, p.fecha_publicacion, u.nombre AS autor, a.nombre AS animal FROM publicaciones p JOIN usuarios u ON p.id_usuario_publicador = u.id_usuario JOIN animales a ON p.id_animal = a.id_animal ORDER BY p.fecha_publicacion DESC";
$result = $conexion->query($sql);
?>
<title>Gestionar Publicaciones - Admin</title>
<h2>Gestionar Publicaciones</h2>
<table><thead><tr><th>Título</th><th>Animal</th><th>Autor</th><th>Fecha</th><th>Acción</th></tr></thead><tbody>
<?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['titulo']); ?></td>
        <td><?php echo htmlspecialchars($row['animal']); ?></td>
        <td><?php echo htmlspecialchars($row['autor']); ?></td>
        <td><?php echo $row['fecha_publicacion']; ?></td>
        <td><form method="post" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta publicación? Esta acción es irreversible.');"><input type="hidden" name="delete_id" value="<?php echo $row['id_publicacion']; ?>"><button type="submit" style="background: #E57373; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px;">Eliminar</button></form></td>
    </tr>
<?php endwhile; ?>
</tbody></table>
<?php include 'admin_footer.php'; ?> <!-- Esta ruta ahora es correcta -->