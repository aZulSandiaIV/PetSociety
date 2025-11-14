<?php
    include "../../config.php";

    $especies = [];

    $result = $conexion->query("SELECT especie, COUNT(*) as total FROM animales GROUP BY especie");
    if ($result === false) {
        http_response_code(500); // Internal Server Error
        error_log("SQL error en solicitar/estadisticas/especies.php: " . $conexion->error);
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
        $especies[] = [
            'especie' => $row['especie'],
            'total' => $row['total']
        ];
    }

    $result->free();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($especies);
?>