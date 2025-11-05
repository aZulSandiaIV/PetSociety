ALTER TABLE `publicaciones`
ADD `latitud` DECIMAL(10,8) NULL DEFAULT NULL AFTER `tipo_publicacion`,
ADD `longitud` DECIMAL(11,8) NULL DEFAULT NULL AFTER `latitud`;


CREATE TABLE contacto (
  id_contacto int(11) NOT NULL AUTO_INCREMENT,
  email varchar(100) NOT NULL,
  comentario text NOT NULL,
  fecha_envio timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id_contacto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;


ALTER TABLE `avistamientos`
ADD COLUMN `estado` ENUM('Visto', 'Ya no está') NOT NULL DEFAULT 'Visto' AFTER `id_usuario_reporta`,
ADD INDEX `idx_avistamientos_estado` (`estado`);

-- Añadir columnas de tamaño, edad y color a la tabla 'animales'
ALTER TABLE `animales`
ADD COLUMN `tamaño` ENUM('Pequeño', 'Mediano', 'Grande') NULL DEFAULT NULL AFTER `raza`,
ADD COLUMN `color` VARCHAR(50) NULL DEFAULT NULL AFTER `tamaño`,
CHANGE COLUMN `edad` `edad` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Edad aproximada (ej: Cachorro, 3 años)';

-- Añadir columna de texto para la ubicación en 'publicaciones' para facilitar la búsqueda
ALTER TABLE `publicaciones`
ADD COLUMN `ubicacion_texto` VARCHAR(255) NULL DEFAULT NULL AFTER `contenido`;

-- Añadir columna para la foto de perfil en 'usuarios'
ALTER TABLE `usuarios` ADD `foto_perfil_url` VARCHAR(255) NULL DEFAULT NULL AFTER `direccion`;
