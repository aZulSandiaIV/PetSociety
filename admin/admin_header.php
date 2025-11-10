<?php
session_start();
require_once "../config.php";

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || 
    !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - PetSociety</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="../index.php"><img src="../img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="../index.php">PetSociety</a></h1>
            </div>
            <nav>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-menu">
                    <li><a href="../index.php">Inicio</a></li>
                    <li><a href="../refugios.php">Refugios</a></li>
                    <li><a href="../buzon.php">Mensajes</a></li>
                    <li class="admin-panel-dropdown">
                        <span class="admin-panel-trigger">Panel de Administrador</span>
                        <div class="admin-submenu">
                            <ul>
                                <li><a href="statistics.php">Estadísticas</a></li>
                                <li><a href="manage_publications.php">Administrar Publicaciones</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="user-menu mobile-user-menu">
                        <span class="user-menu-trigger">
                            <span class="user-icon"></span>
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                        </span>
                        <div class="dropdown-menu">
                            <ul>
                                <li><a href="../mi_perfil.php">Mi Perfil</a></li>
                                <li><a href="../logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["nombre"]); ?>. Gestiona las funciones administrativas de PetSociety.</p>
        </div>

        <div class="admin-nav">
            <a href="statistics.php" class="admin-nav-tab <?php echo basename($_SERVER['PHP_SELF']) == 'statistics.php' ? 'active' : ''; ?>">
                📊 Estadísticas
            </a>
            <a href="manage_publications.php" class="admin-nav-tab <?php echo basename($_SERVER['PHP_SELF']) == 'manage_publications.php' ? 'active' : ''; ?>">
                📝 Administrar Publicaciones
            </a>
        </div>