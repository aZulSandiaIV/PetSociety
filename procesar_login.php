<?php
// Iniciar la sesión
session_start();

// Incluir el archivo de configuración
require_once "config.php";

// Variables
$email = $password = "";
$email_err = $password_err = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, ingresa tu email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validar contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, ingresa tu contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Si no hay errores, verificar credenciales
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id_usuario, nombre, email, password_hash, es_refugio, is_admin FROM usuarios WHERE email = ?";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $nombre, $email_db, $hashed_password, $es_refugio, $is_admin);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Contraseña correcta, iniciar sesión
                            session_start();
                            
                            // Almacenar datos en variables de sesión
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_usuario"] = $id;
                            $_SESSION["nombre"] = $nombre;
                            $_SESSION["is_admin"] = $is_admin;
                            $_SESSION["es_refugio"] = $es_refugio;
                            
                            // Redirigir según el rol
                            if ($is_admin == 1) {
                                header("location: admin/index.php");
                            } else {
                                header("location: index.php");
                            }
                        } else {
                            // Mostrar un error si la contraseña no es válida
                            echo "La contraseña que ingresaste no es válida.";
                        }
                    }
                } else {
                    // Mostrar un error si el email no existe
                    echo "No se encontró ninguna cuenta con ese email.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            }
            $stmt->close();
        }
    }
    $conexion->close();
}
?>