<?php
session_start();
require_once "config.php";

// 1. Proteger la ruta: solo para usuarios logueados
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 2. Validar los parámetros de entrada
if (!isset($_GET['id']) || !isset($_GET['accion']) || !isset($_GET['redirect'])) {
    die("Error: Faltan parámetros.");
}

$redirect_page = $_GET['redirect'];
$id_avistamiento = intval($_GET['id']);
$accion = $_GET['accion'];

$sql = "";

// 3. Determinar la acción a realizar
if ($accion == 'sigue_aqui') {
    // Si el animal sigue aquí, actualizamos la fecha para que aparezca como reciente
    $sql = "UPDATE avistamientos SET fecha_avistamiento = CURRENT_TIMESTAMP WHERE id_avistamiento = ?";
} elseif ($accion == 'no_esta') {
    // Si ya no está, cambiamos su estado para que no se muestre más en el mapa
    $sql = "UPDATE avistamientos SET estado = 'Ya no está' WHERE id_avistamiento = ?";
} else {
    die("Error: Acción no válida.");
}

// 4. Ejecutar la consulta
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $id_avistamiento);
    
    if ($stmt->execute()) {
        // Redirigir a la página de origen
        header("location: " . $redirect_page . "?reporte=exito");
    } else {
        echo "Error al actualizar el reporte.";
    }
    $stmt->close();
}

$conexion->close();
exit;
?>
