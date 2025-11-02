<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";
require_once "funciones.php";
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
    <title>Mi Perfil - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="perfil.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-menu">
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="buzon.php">Mensajes</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="user-menu mobile-user-menu">
                            <span class="user-menu-trigger">
                                <span class="user-icon"></span>
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                            </span>
                            <div class="dropdown-menu">
                                <ul>
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                        <li><a href="admin/index.php" class="admin-panel-link">Panel Admin</a></li>
                                    <?php endif; ?>
                                    <li><a href="mi_perfil.php">Mi Perfil</a></li>
                                    <li><a href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
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
<?php 
                    $foto_perfil = obtenerFotoPerfil($usuario['foto_perfil_url'], $usuario['nombre'], $id_usuario);
                    if ($foto_perfil['tipo'] === 'foto'): 
                    ?>
                        <div class="profile-picture-container">
                            <img src="<?php echo htmlspecialchars($foto_perfil['url']); ?>" alt="Foto de perfil">
                        </div>
                    <?php else: ?>
                        <div class="profile-picture-container">
                            <div class="profile-avatar" style="background-color: <?php echo $foto_perfil['color']; ?>">
                                <?php echo htmlspecialchars($foto_perfil['iniciales']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="profile-picture-upload">
                        <form action="actualizar_foto_perfil.php" method="post" enctype="multipart/form-data" class="upload-form" id="upload-form">
                            <div class="file-input-wrapper" onclick="document.getElementById('foto_perfil').click()">
                                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" required style="display: none;">
                                📷 Seleccionar foto
                            </div>
                            <div id="file-selected" style="display: none;"></div>
                            <button type="submit" class="upload-btn" id="upload-btn" disabled>Actualizar Foto</button>
                        </form>
                        
                        <?php if (!empty($usuario['foto_perfil_url'])): ?>
                            <form action="actualizar_foto_perfil.php" method="post" style="margin-top: 10px;">
                                <input type="hidden" name="eliminar_foto" value="1">
                                <button type="submit" class="delete-btn" onclick="return confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')">
                                    🗑️ Eliminar Foto
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de la selección de archivos para la foto de perfil
            const fileInput = document.getElementById('foto_perfil');
            const uploadBtn = document.getElementById('upload-btn');
            const fileSelected = document.getElementById('file-selected');

            if (fileInput && uploadBtn) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        const fileName = this.files[0].name;
                        uploadBtn.disabled = false;
                        if (fileSelected) {
                            fileSelected.textContent = 'Archivo: ' + fileName;
                            fileSelected.style.display = 'block';
                        }
                    } else {
                        uploadBtn.disabled = true;
                        if (fileSelected) {
                            fileSelected.style.display = 'none';
                        }
                    }
                });
            }

            // Manejo del menú móvil
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const mobileUserMenu = document.querySelector('.mobile-user-menu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });

                document.addEventListener('click', function(event) {
                    if (!navMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    }
                });

                const navLinks = navMenu.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    });
                });
            }

            if (mobileUserMenu) {
                const userMenuTrigger = mobileUserMenu.querySelector('.user-menu-trigger');
                if (userMenuTrigger) {
                    userMenuTrigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        mobileUserMenu.classList.toggle('active');
                    });
                }
            }
        });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>