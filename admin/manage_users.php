<?php
include 'admin_header.php';

// Lógica para "eliminar" un usuario (soft delete)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_usuario_a_borrar = intval($_POST['delete_id']);

    // Verificar que no se esté intentando borrar al usuario actual
    if ($id_usuario_a_borrar == $_SESSION['id_usuario']) {
        echo "<div class='admin-alert warning'>⚠️ No puedes desactivar tu propia cuenta.</div>";
    } else {
        // Preparar la consulta para el soft delete
        $sql_delete = "UPDATE usuarios SET is_active = 0 WHERE id_usuario = ?";
        if ($stmt_del = $conexion->prepare($sql_delete)) {
            $stmt_del->bind_param("i", $id_usuario_a_borrar);
            if ($stmt_del->execute()) {
                echo "<div class='admin-alert success'>✅ Usuario desactivado correctamente.</div>";
            } else {
                echo "<div class='admin-alert error'>❌ Error al desactivar el usuario.</div>";
            }
            $stmt_del->close();
        }
    }
}

// Lógica para cambiar el rol de administrador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_role_id'])) {
    $id_usuario_a_cambiar_rol = intval($_POST['change_role_id']);
    $nuevo_rol = intval($_POST['new_role']); // 1 para admin, 0 para no admin

    // Verificar que no se esté intentando cambiar el rol al usuario actual
    if ($id_usuario_a_cambiar_rol == $_SESSION['id_usuario']) {
        echo "<div class='admin-alert warning'>⚠️ No puedes cambiar tu propio rol de administrador.</div>";
    } else {
        // Preparar la consulta para cambiar el rol
        $sql_change_role = "UPDATE usuarios SET is_admin = ? WHERE id_usuario = ?";
        if ($stmt_role = $conexion->prepare($sql_change_role)) {
            $stmt_role->bind_param("ii", $nuevo_rol, $id_usuario_a_cambiar_rol);
            if ($stmt_role->execute()) {
                echo "<div class='admin-alert success'>✅ Rol de usuario actualizado correctamente.</div>";
            } else {
                echo "<div class='admin-alert error'>❌ Error al actualizar el rol del usuario.</div>";
            }
            $stmt_role->close();
        }
    }
}

// Consulta para obtener todos los usuarios activos con su fecha de registro
$sql = "SELECT id_usuario, nombre, email, es_refugio, is_admin, fecha_registro FROM usuarios WHERE is_active = 1 ORDER BY fecha_registro DESC";
$result = $conexion->query($sql);
?>

<div class="admin-table-container">
    <h3>Lista de Usuarios Activos</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha de Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php 
                            if ($row['is_admin']) {
                                echo '<strong>Admin</strong>';
                            } elseif ($row['es_refugio']) {
                                echo 'Refugio';
                            } else {
                                echo 'Usuario';
                            }
                        ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                    <td>
                        <form method="post" class="admin-inline-form" onsubmit="return confirm('¿Estás seguro de que quieres desactivar este usuario?');">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id_usuario']; ?>">
                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-small">
                                🗑️ Desactivar
                            </button>
                        </form>
                        <form method="post" class="admin-inline-form">
                            <input type="hidden" name="change_role_id" value="<?php echo $row['id_usuario']; ?>">
                            <select name="new_role" class = "admin-btn-small">
                                <option value="1" <?php echo ($row['is_admin'] == 1) ? 'selected' : ''; ?>>Admin</option>
                                <option value="0" <?php echo ($row['is_admin'] == 0) ? 'selected' : ''; ?>>No Admin</option>
                            </select>
                            <button type="submit" class="admin-btn admin-btn-small">Cambiar Rol</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>

<style>
    select.admin-btn-small {
        background-color: #f2f2f2;
        color: #404040;
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.8rem;
    }
</style>