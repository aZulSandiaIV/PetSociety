<?php
session_start();
require_once "config.php";
require_once "funciones.php";

// --- Obtener todos los refugios usando la nueva función ---
$refugios = obtener_refugios($conexion);

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refugios - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="refugio.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    
    <script src="js/CargaAsync.js"></script>
</head>
<body>
    <?php include 'header_estandar.php'; ?>

    <div class="container">
        <h2>Refugios de Animales</h2>
        <p>Estos son los refugios que colaboran con nosotros. Puedes contactarlos o ver los animales que tienen en adopción.</p>

        <div id="refugios-container" class="refugios-container">
            <!-- Las tarjetas de refugios se cargarán aquí dinámicamente -->
            
        </div>
        
        <div class="btn-container">
            <button id="cargar-mas-btn" class="btn load-more-btn">Cargar Más</button>
        </div> 

    </div>

    
    <script src="js/refugios.js"></script>
    <script src="funciones_js.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Llama a la función para la interactividad de los menús
            interactividad_menus();
        });
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>
