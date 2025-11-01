<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";
$id_usuario = $_SESSION['id_usuario'];

// --- Obtener datos del usuario ---
$sql_usuario = "SELECT nombre, email, telefono, dni, foto_perfil_url FROM usuarios WHERE id_usuario = ?";
$usuario = null;
if ($stmt_usuario = $conexion->prepare($sql_usuario)) {
    $stmt_usuario->bind_param("i", $id_usuario);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    if ($result_usuario->num_rows == 1) {
        $usuario = $result_usuario->fetch_assoc();
    }
    $stmt_usuario->close();
}

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
    <link rel="stylesheet" href="perfil.css">
    <style>
        .profile-info {
            display: flex;
            align-items: flex-start;
            gap: 30px;
        }
        .profile-picture {
            flex-shrink: 0;
        }
        .profile-picture img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
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

    <div class="container">
        <!-- Mostrar mensajes de éxito o error -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje <?php echo $_SESSION['tipo_mensaje']; ?>">
                <?php 
                echo htmlspecialchars($_SESSION['mensaje']); 
                unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Sección de Información Personal -->
        <div class="profile-section">
            <div class="profile-info">
                <div class="profile-picture">
                    <img src="<?php echo !empty($usuario['foto_perfil_url']) ? htmlspecialchars($usuario['foto_perfil_url']) : 'img/perfil_default.png'; ?>" alt="Foto de perfil">
                </div>

                <div class="user-details">
                    <h3>Mi Información Personal</h3>
                    
                    <!-- Vista de solo lectura -->
                    <div id="profile-view">
                        <div class="field-group">
                            <label>Nombre:</label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" readonly>
                        </div>
                        <div class="field-group">
                            <label>Email:</label>
                            <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                        </div>
                        <div class="field-group">
                            <label>Teléfono:</label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" readonly>
                        </div>
                        <div class="field-group">
                            <label>DNI:</label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['dni']); ?>" readonly>
                        </div>
                    </div>

                    <!-- Formulario de edición -->
                    <form id="edit-form" class="edit-form" method="POST" action="actualizar_perfil.php">
                        <div class="field-group">
                            <label>Nombre:</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="field-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        <div class="field-group">
                            <label>Teléfono:</label>
                            <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" placeholder="Opcional">
                        </div>
                        <div class="field-group">
                            <label>DNI:</label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['dni']); ?>" readonly>
                            <small style="color: #666;">El DNI no se puede modificar por seguridad.</small>
                        </div>
                        <button type="submit" class="save-button">Guardar cambios</button>
                        <button type="button" class="cancel-button" onclick="cancelEdit()">Cancelar</button>
                    </form>
                </div>
                
                <div class="profile-actions">
                    <button class="edit-button" onclick="enableEdit()">Editar información</button>
                </div>
            </div>
        </div>

        <?php if (!empty($publicaciones)): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Gestionar Mis Publicaciones</h2>
                <a href="publicar.php" class="btn nueva-publicacion-btn">🐾 Nueva Publicación</a>
            </div>
            <?php foreach ($publicaciones as $pub): ?>
                <div class="pub-card">
                    <div class="pub-info">
                        <h3><?php echo htmlspecialchars($pub['titulo']); ?> (<?php echo htmlspecialchars($pub['nombre']); ?>)</h3>
                        <span class="pub-estado <?php if($pub['estado'] == 'Adoptado' || $pub['estado'] == 'Encontrado') echo 'estado-'.$pub['estado']; ?>">
                            Estado: <?php echo htmlspecialchars($pub['estado']); ?>
                        </span>
                    </div>
                    <div class="pub-actions">
                        <?php if ($pub['estado'] == 'En Adopción'): ?>
                            <a href="gestionar_adopcion.php?id_pub=<?php echo $pub['id_publicacion']; ?>&id_animal=<?php echo $pub['id_animal']; ?>" class="btn">Gestionar Adopción</a>
                        <?php elseif ($pub['estado'] == 'Hogar Temporal'): ?>
                            <a href="gestionar_adopcion.php?id_pub=<?php echo $pub['id_publicacion']; ?>&id_animal=<?php echo $pub['id_animal']; ?>" class="btn">Gestionar Hogar</a>
                        <?php elseif ($pub['estado'] == 'Perdido'): ?>
                            <form method="post" style="display: inline;">
                                <a href="ver_reportes.php?id_animal=<?php echo $pub['id_animal']; ?>" class="btn" style="background-color: #97BC62;">Ver Avistamientos</a>
                                <input type="hidden" name="id_animal" value="<?php echo $pub['id_animal']; ?>">
                                <button type="submit" name="marcar_encontrado" class="btn" onclick="return confirm('¿Estás seguro de que has encontrado a tu mascota?');">Marcar como Encontrado</button>
                            </form>
                        <?php elseif ($pub['estado'] == 'Encontrado'): ?>
                            <span>Publicación activa</span>
                        <?php else: ?>
                            <span>Gestión finalizada</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <h2>Gestionar Mis Publicaciones</h2>
            <div class="no-publications">
                <h3>¡Aún no tienes publicaciones!</h3>
                <p>Comienza a ayudar a las mascotas publicando tu primera historia.</p>
                <a href="publicar.php" class="btn">🐾 Crear mi primera publicación</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function enableEdit() {
            document.getElementById('profile-view').style.display = 'none';
            document.getElementById('edit-form').style.display = 'block';
            document.querySelector('.edit-button').style.display = 'none';
        }

        function cancelEdit() {
            document.getElementById('profile-view').style.display = 'block';
            document.getElementById('edit-form').style.display = 'none';
            document.querySelector('.edit-button').style.display = 'inline-block';
        }
    </script>
</body>
</html>