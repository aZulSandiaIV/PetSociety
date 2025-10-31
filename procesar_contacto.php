<?php
require_once "config.php";

$email = $comentario = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validar y limpiar email
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Por favor, ingresa tu email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El formato del email no es válido.";
    } else {
        $email = trim($_POST["email"]);
    }

    // 2. Validar y limpiar comentario
    if (empty(trim($_POST["comentario"]))) {
        $errors[] = "Por favor, escribe un comentario.";
    } else {
        $comentario = trim($_POST["comentario"]);
    }

    // 3. Si no hay errores, insertar en la base de datos
    if (empty($errors)) {
        $sql = "INSERT INTO contacto (email, comentario) VALUES (?, ?)";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ss", $email, $comentario);

            if ($stmt->execute()) {
                // Éxito: redirigir o mostrar mensaje
                echo "¡Gracias por tu comentario! Hemos recibido tu sugerencia. Serás redirigido en 5 segundos.";
                header("refresh:5;url=index.php");
                exit();
            } else {
                $errors[] = "Algo salió mal al guardar tu comentario. Por favor, inténtalo de nuevo más tarde.";
            }
            $stmt->close();
        }
    }

    // Si hubo errores, mostrarlos
    if (!empty($errors)) {
        echo "<h2>Error al enviar el formulario:</h2>";
        foreach ($errors as $error) {
            echo "<p>" . htmlspecialchars($error) . "</p>";
        }
        echo '<a href="contacto.php">Volver al formulario</a>';
    }

    $conexion->close();
} else {
    // Si alguien accede directamente, lo redirigimos
    header("location: contacto.php");
    exit;
}
?>
