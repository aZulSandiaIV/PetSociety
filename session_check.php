<?php
ob_start(); // atrapar cualquier output accidental (warnings, echo, BOM)
session_start();

header('Content-Type: application/json; charset=utf-8');

$response = [
    'loggedin' => false,
    'user' => null
];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401); // Unauthorized
    // limpiar buffer y registrar si hubo salida no esperada
    $buffer = ob_get_clean();
    if (trim($buffer) !== '') {
        error_log("session_check.php unexpected output for unauthenticated user: " . $buffer);
    }
    echo json_encode($response);
    exit;
}

$userData = [
    'id_usuario' => isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null,
];

// Si hay conexión a BD disponible, intentar obtener datos actualizados del usuario
if (!empty($userData['id_usuario']) && file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php'; // debe definir $conexion (mysqli)

    if (isset($conexion) && $conexion instanceof mysqli) {
        $stmt = $conexion->prepare('SELECT id_usuario, nombre, email, es_refugio FROM usuarios WHERE id_usuario = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('i', $userData['id_usuario']);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    // mapear campos permitidos (sin incluir contraseñas u otros sensibles)
                    $userData = [
                        'id_usuario'  => (int)$row['id_usuario'],
                        'nombre'      => isset($row['nombre']) ? $row['nombre'] : null,
                        'email'      => isset($row['email']) ? $row['email'] : null,
                        'es_refugio'  => isset($row['es_refugio']) ? (int)$row['es_refugio'] : 0,
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
    if (isset($_SESSION['email'])) $userData['email'] = $_SESSION['email'];
    if (isset($_SESSION['es_refugio'])) $userData['es_refugio'] = (int)$_SESSION['es_refugio'];
}

$response['loggedin'] = true;
$response['user'] = $userData;

// limpiar cualquier salida capturada y registrarla (no enviarla al cliente)
$buffer = ob_get_clean();
if (trim($buffer) !== '') {
    error_log("session_check.php unexpected output for user {$userData['id_usuario']}: " . $buffer);
}

echo json_encode($response);
exit;
?>