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
