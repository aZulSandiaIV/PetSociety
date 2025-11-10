<?php
//Tener en cuenta, esto es un port. un horrible port hecho con sueño.

    if (!empty($_GET['perfil']) && file_exists($_GET['perfil'])) {
        return [
            'tipo' => 'foto',
            'url' => $$_GET['perfil'],
            'iniciales' => '',
            'color' => ''
        ];
    }
    
    return [
        'tipo' => 'avatar',
        'url' => '',
        'iniciales' => generarIniciales($_GET['nombre']),
        'color' => generarColorAvatar($_GET['nombre'] . $_GET['id'])
    ];

    

?>