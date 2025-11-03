<?php
    $filtro_estado = $_GET['filtro'] ?? 'Todos';
    $allowed_status_filters = ['Adopción', 'Hogar Temporal', 'Perdido'];
    $allowed_races_filters = ['Perro', 'Gato', 'Otro'];
    $allowed_sizes_filters = ['Pequeño', 'Mediano', 'Grande'];
?>