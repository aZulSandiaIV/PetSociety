<?php
session_start(); 
include ('set_bdd.php');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email' AND password_hash = '$password'");
$resultados = mysqli_num_rows($query);

if ($resultados != 0) {
    $datos = mysqli_fetch_array($query);

    $_SESSION["nombre"] = $datos['nombre'];
    $_SESSION["email"] = $datos['email'];
    $_SESSION["loggedin"] = true;

    header("Location: index.php");
    exit;
} else {
    echo "Error: Credenciales inválidas.";
}
?>