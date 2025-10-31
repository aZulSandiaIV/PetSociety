<?php

    $dato = "<p>error</p>";

    $json = json_encode($dato);
    header('Content-Type: application/json');
    echo $json;

?>