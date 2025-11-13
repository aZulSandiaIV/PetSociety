<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// 1. Validar que se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No se especificó una publicación para editar.");
}
$id_publicacion = intval($_GET['id']);
$id_usuario_actual = $_SESSION['id_usuario'];

// 2. Obtener los datos de la publicación y del animal asociado
$sql = "SELECT p.*, a.* 
        FROM publicaciones p
        JOIN animales a ON p.id_animal = a.id_animal
        WHERE p.id_publicacion = ?";

$publicacion = null;
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $id_publicacion);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $publicacion = $result->fetch_assoc();
    } else {
        die("Error: La publicación no existe.");
    }
    $stmt->close();
}

// 3. Verificar permisos: solo el autor puede editar
if ($publicacion['id_usuario_publicador'] !== $id_usuario_actual) {
    die("Acceso denegado. No tienes permiso para editar esta publicación.");
}

// Aquí iría la lógica para procesar el formulario cuando se envíe (POST)
// Por ahora, solo mostramos el formulario.

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación - PetSociety</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="estilos.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <style>
        /* Estilos para los botones de acción del formulario */
        .form-group.action-buttons {
            display: flex;
            gap: 15px; /* Aumenta el espacio entre los botones */
            flex-wrap: wrap;
        }
        .form-group.action-buttons .btn {
            flex-grow: 0; /* Evita que los botones se estiren */
            padding: 10px 20px; /* Ajusta el tamaño del botón */
            font-size: 1em; /* Mantiene el tamaño de fuente estándar */
        }
        .form-group.action-buttons .btn-secondary {
            background-color: #6c757d; /* Color gris para 'Cancelar' */
            color: #ffffff !important; /* Asegura que el texto sea blanco */
        }
        /* Estilo para el mapa en el formulario de edición */
        #mapa-seleccion { 
            height: 350px; margin-top: 15px; border-radius: 8px; z-index: 1; 
        }
    </style>
</head>
<body>
    <?php include 'header_estandar.php'; ?>
    <div class="form-container">
        <h2>Editar Publicación</h2>
        
        <!-- El action apuntará a un futuro 'procesar_edicion.php' o a este mismo archivo -->
        <form action="procesar_edicion_publicacion.php" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para enviar el ID de la publicación -->
            <input type="hidden" name="id_publicacion" value="<?php echo $publicacion['id_publicacion']; ?>">
            <input type="hidden" name="id_animal" value="<?php echo $publicacion['id_animal']; ?>">

            <h3>Datos de la Publicación</h3>
            <div class="form-group">
                <label>Título de la Publicación</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($publicacion['titulo']); ?>" required>
            </div>
            <div class="form-group">
                <label>Tipo de Publicación (No se puede cambiar)</label>
                <input type="text" name="tipo_publicacion" value="<?php echo htmlspecialchars($publicacion['tipo_publicacion']); ?>" readonly>
                <small>El tipo de publicación no se puede modificar para mantener la consistencia.</small>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="contenido" rows="5" required><?php echo htmlspecialchars($publicacion['contenido']); ?></textarea>
            </div>

            <!-- Campos específicos para 'Perdido' -->
            <?php if ($publicacion['tipo_publicacion'] == 'Perdido'): ?>
                <div class="form-group">
                    <label>Ubicación donde se perdió (Mapa)</label>
                    <input type="text" name="ubicacion_texto" id="ubicacion_texto" value="<?php echo htmlspecialchars($publicacion['ubicacion_texto'] ?? ''); ?>" placeholder="Arrastra el marcador en el mapa o usa el botón" required readonly>
                    <button type="button" id="usar-ubicacion-actual" class="btn" style="width: auto; margin-top: 5px; background-color: #97BC62;">Usar mi ubicación actual</button>
                    <!-- Campos ocultos para las coordenadas -->
                    <input type="hidden" name="latitud" id="latitud" value="<?php echo htmlspecialchars($publicacion['latitud'] ?? ''); ?>">
                    <input type="hidden" name="longitud" id="longitud" value="<?php echo htmlspecialchars($publicacion['longitud'] ?? ''); ?>">
                    <!-- Contenedor para el mapa interactivo -->
                    <div id="mapa-seleccion"></div>
                </div>
                 <div class="form-group">
                    <label>Características distintivas</label>
                    <textarea name="caracteristicas_distintivas" rows="3"><?php echo htmlspecialchars($publicacion['caracteristicas_distintivas'] ?? ''); ?></textarea>
                </div>
            <?php endif; ?>

            <hr style="margin: 20px 0;">

            <h3>Datos del Animal</h3>
            <div class="form-group">
                <label>Nombre del Animal</label>
                <input type="text" name="nombre_animal" value="<?php echo htmlspecialchars($publicacion['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Especie</label>
                <input type="text" name="especie" value="<?php echo htmlspecialchars($publicacion['especie']); ?>" required>
            </div>
            <div class="form-group">
                <label>Raza</label>
                <input type="text" name="raza" value="<?php echo htmlspecialchars($publicacion['raza']); ?>">
            </div>
            <div class="form-group">
                <label>Edad Aproximada</label>
                <input type="text" name="edad" value="<?php echo htmlspecialchars($publicacion['edad']); ?>">
            </div>
            <div class="form-group">
                <label>Tamaño</label>
                <select name="tamaño">
                    <option value="" <?php echo ($publicacion['tamaño'] == '') ? 'selected' : ''; ?>>-- No especificado --</option>
                    <option value="Pequeño" <?php echo ($publicacion['tamaño'] == 'Pequeño') ? 'selected' : ''; ?>>Pequeño</option>
                    <option value="Mediano" <?php echo ($publicacion['tamaño'] == 'Mediano') ? 'selected' : ''; ?>>Mediano</option>
                    <option value="Grande" <?php echo ($publicacion['tamaño'] == 'Grande') ? 'selected' : ''; ?>>Grande</option>
                </select>
            </div>
            <div class="form-group">
                <label>Color Principal</label>
                <input type="text" name="color" value="<?php echo htmlspecialchars($publicacion['color']); ?>">
            </div>
            <div class="form-group">
                <label>Género</label>
                <select name="genero" required>
                    <option value="Macho" <?php echo ($publicacion['genero'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                    <option value="Hembra" <?php echo ($publicacion['genero'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                </select>
            </div>
            <div class="form-group">
                <label>Foto Actual</label>
                <?php if (!empty($publicacion['imagen_url'])): ?>
                    <img src="<?php echo htmlspecialchars($publicacion['imagen_url']); ?>" alt="Foto actual" style="max-width: 200px; border-radius: 8px; margin-bottom: 10px;">
                <?php else: ?>
                    <p>No hay foto actualmente.</p>
                <?php endif; ?>
                <label>Cambiar Foto (opcional)</label>
                <input type="file" name="foto_animal" accept="image/jpeg, image/png, image/gif">
            </div>
            <div class="form-group action-buttons">
                <input type="submit" class="btn" value="Guardar Cambios">
                <a href="mi_perfil.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Scripts de Leaflet y de la aplicación -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="Geolocalizacion.js"></script>
    <script src="funciones_js.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Solo inicializar el mapa si existe el contenedor (para publicaciones de tipo 'Perdido')
            if (document.getElementById('mapa-seleccion')) {
                const latInicial = document.getElementById('latitud').value;
                const lonInicial = document.getElementById('longitud').value;
                
                const mapaInfo = inicializarMapaDeSeleccion('mapa-seleccion', 'latitud', 'longitud', 'ubicacion_texto', latInicial, lonInicial);
                usar_ubicacion_actual(mapaInfo);
            }
        });
    </script>
</body>
</html>