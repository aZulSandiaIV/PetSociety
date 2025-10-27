<?php
session_start();
// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("location: ../login.php"); // Esta ruta ahora es correcta
    exit;
}
require_once "../config.php"; // Esta ruta ahora es correcta
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../estilos.css"> <!-- Esta ruta ahora es correcta -->
    <style>
        .admin-nav { background-color: #f4f4f4; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .admin-nav a { margin-right: 15px; text-decoration: none; color: #2C5F2D; font-weight: bold; }
        .admin-nav a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #2C5F2D; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding"><h1><a href="index.php">Panel de Administración</a></h1></div>
            <nav><ul><li><a href="../index.php">Ver Sitio</a></li><li><a href="../logout.php">Cerrar Sesión</a></li></ul></nav>
        </div>
    </header>
    <div class="container">