<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(['loggedin' => false]);
    exit;
}

$userData = [
    'id_usuario' => isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null,
];

// Si hay conexión a BD disponible, intentar obtener datos actualizados del usuario
if (!empty($userData['id_usuario']) && file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php'; // debe definir $conexion (mysqli)

    if (isset($conexion) && $conexion instanceof mysqli) {
        $stmt = $conexion->prepare('SELECT id_usuario, nombre, correo, es_refugio FROM usuarios WHERE id_usuario = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('i', $userData['id_usuario']);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    // mapear campos permitidos (sin incluir contraseñas u otros sensibles)
                    $userData = [
                        'id_usuario' => (int)$row['id_usuario'],
                        'nombre' => isset($row['nombre']) ? $row['nombre'] : null,
                        'correo' => isset($row['correo']) ? $row['correo'] : null,
                        'es_refugio' => isset($row['es_refugio']) ? (int)$row['es_refugio'] : 0,
                    ];
                }
                $res->free();
            }
            $stmt->close();
        }
    }
} else {
    // Relleno con otros datos de sesión si existen
    if (isset($_SESSION['nombre'])) $userData['nombre'] = $_SESSION['nombre'];
    if (isset($_SESSION['correo'])) $userData['correo'] = $_SESSION['correo'];
    if (isset($_SESSION['es_refugio'])) $userData['es_refugio'] = (int)$_SESSION['es_refugio'];
}

echo json_encode([
    'loggedin' => true,
    'user' => $userData
]);
?>