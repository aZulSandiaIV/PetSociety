<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_animal = intval($_POST['id_animal']);
    $id_usuario_reportador = $_SESSION['id_usuario'];
    $ultima_ubicacion = trim($_POST['ultima_ubicacion_vista']);
    $caracteristicas = trim($_POST['caracteristicas_distintivas']);

    if (empty($id_animal) || empty($ultima_ubicacion)) {
        die("Error: Faltan datos obligatorios.");
    }

    $sql = "INSERT INTO reportes_perdidos (id_animal, id_usuario_reportador, ultima_ubicacion_vista, caracteristicas_distintivas) VALUES (?, ?, ?, ?)";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("iiss", $id_animal, $id_usuario_reportador, $ultima_ubicacion, $caracteristicas);
        if ($stmt->execute()) {
            echo "¡Gracias por tu ayuda! Tu reporte ha sido enviado al dueño. Serás redirigido en 3 segundos.";
            header("refresh:3;url=index.php");
        } else {
            echo "Error al guardar el reporte.";
        }
        $stmt->close();
    }
    $conexion->close();
}