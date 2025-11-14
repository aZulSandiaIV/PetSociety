<?php
    include "../../config.php";

    $estados = [];

    $result = $conexion->query("SELECT estado, COUNT(*) as total FROM animales GROUP BY estado");
    if ($result === false) {
        http_response_code(500); // Internal Server Error
        error_log("SQL error en solicitar/estadisticas/estados.php: " . $conexion->error);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Database error']);
        exit;
    }

    if ($result->num_rows === 0) {
        http_response_code(204); // No Content
        $result->free();
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        $estados[] = [
            'estado' => $row['estado'],
            'total' => $row['total']
        ];
    }

    $result->free();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($estados);

?>