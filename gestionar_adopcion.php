<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { header("location: login.php"); exit; }
require_once "config.php";

$id_publicacion = intval($_GET['id_pub']);
$id_animal = intval($_GET['id_animal']);
$id_publicador = $_SESSION['id_usuario'];

// --- Lógica para APROBAR la adopción ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aprobar_adopcion'])) {
    $id_adoptante = intval($_POST['id_adoptante']);

    $conexion->begin_transaction();
    try {
        // 1. Actualizar el estado del animal a 'Adoptado'
        $sql_update_animal = "UPDATE animales SET estado = 'Adoptado' WHERE id_animal = ?";
        $stmt_update = $conexion->prepare($sql_update_animal);
        $stmt_update->bind_param("i", $id_animal);
        $stmt_update->execute();
        $stmt_update->close();

        // 2. Insertar el registro en la tabla de adopciones
        $sql_insert_adopcion = "INSERT INTO adopciones (id_animal, id_usuario_adoptante, fecha_adopcion) VALUES (?, ?, CURDATE())";
        $stmt_insert = $conexion->prepare($sql_insert_adopcion);
        $stmt_insert->bind_param("ii", $id_animal, $id_adoptante);
        $stmt_insert->execute();
        $stmt_insert->close();

        $conexion->commit();
        echo "<div class='alert' style='background-color: #d4edda; color: #155724;'>¡Adopción completada con éxito!</div>";

    } catch (Exception $e) {
        $conexion->rollback();
        die("Error al procesar la adopción: " . $e->getMessage());
    }
}

// --- Obtener usuarios que han contactado por esta publicación ---
// Buscamos mensajes cuyo asunto fue generado por enviar_mensaje.php
$sql_interesados = "SELECT DISTINCT u.id_usuario, u.nombre 
                    FROM usuarios u 
                    JOIN mensajes m ON u.id_usuario = m.id_remitente
                    JOIN publicaciones p ON m.asunto LIKE CONCAT('%', p.titulo, '%')
                    WHERE p.id_publicacion = ? AND m.id_destinatario = ?";
$interesados = [];
if ($stmt = $conexion->prepare($sql_interesados)) {
    $stmt->bind_param("ii", $id_publicacion, $id_publicador);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $interesados[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Adopción</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo4.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
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
        <h2>Finalizar Proceso de Adopción</h2>
        <p>Selecciona el usuario que adoptará a la mascota. Esta acción es irreversible y marcará la publicación como finalizada.</p>
        
        <?php if (!empty($interesados)): ?>
            <form method="post" onsubmit="return confirm('¿Estás seguro de que quieres aprobar la adopción para este usuario? Esta acción no se puede deshacer.');">
                <div class="form-group">
                    <label for="id_adoptante">Usuarios Interesados:</label>
                    <select name="id_adoptante" id="id_adoptante" required>
                        <option value="">-- Selecciona un usuario --</option>
                        <?php foreach ($interesados as $interesado): ?>
                            <option value="<?php echo $interesado['id_usuario']; ?>"><?php echo htmlspecialchars($interesado['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" name="aprobar_adopcion" class="btn">Aprobar Adopción</button>
                </div>
            </form>
        <?php else: ?>
            <p>Aún no has recibido mensajes de usuarios interesados en esta publicación.</p>
        <?php endif; ?>
        
        <a href="mis_publicaciones.php">Volver a mis publicaciones</a>
    </div>
</body>
</html>