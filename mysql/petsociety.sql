-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-10-2025 a las 23:04:07
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `petsociety`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adopciones`
--

CREATE TABLE `adopciones` (
  `id_adopcion` int(11) NOT NULL,
  `id_animal` int(11) NOT NULL COMMENT 'El animal adoptado (UNIQUE para evitar doble adopción)',
  `id_usuario_adoptante` int(11) DEFAULT NULL,
  `fecha_adopcion` date NOT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `adopciones`
--

INSERT INTO `adopciones` (`id_adopcion`, `id_animal`, `id_usuario_adoptante`, `fecha_adopcion`, `notas`) VALUES
(1, 3, 3, '2025-10-17', NULL),
(2, 1, 2, '2025-10-17', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `animales`
--

CREATE TABLE `animales` (
  `id_animal` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `especie` varchar(50) NOT NULL,
  `raza` varchar(50) DEFAULT NULL,
  `edad` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Edad en años del animal',
  `genero` enum('Macho','Hembra') NOT NULL COMMENT 'Género del animal, estandarizado a Macho o Hembra',
  `descripcion` text NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL COMMENT 'URL o ruta de la imagen del animal',
  `estado` enum('En Adopción','Perdido','Adoptado','Hogar Temporal','Encontrado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `animales`
--

INSERT INTO `animales` (`id_animal`, `nombre`, `especie`, `raza`, `edad`, `genero`, `descripcion`, `imagen_url`, `estado`) VALUES
(1, 'Cartucho', 'Perro', 'Labrador', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f2323b51b6c-Yellow_Labrador_Retriever_2.jpg', 'Adoptado'),
(2, 'desconocido', 'Gato', 'desconocido', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f233113174a-michi estudioso.jpg', 'Encontrado'),
(3, 'Teo', 'Perro', 'caniche', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f2351ad540f-caniche2.jpeg', 'Adoptado'),
(5, 'Dalma', 'Perro', 'Galgo italiano', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f250328e7bc-galgo italiano.jpg', 'En Adopción'),
(6, 'Sofi', 'Gato', 'Estudioso', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f25065a4c23-michi estudioso.jpg', 'En Adopción'),
(7, 'Teo', 'Perro', 'caniche', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f250e99755a-caniche2.jpeg', 'Perdido'),
(8, 'ramoncito', 'Perro', 'Labrador', NULL, 'Macho', 'Descripción pendiente', 'uploads/68f2513a8a92e-Yellow_Labrador_Retriever_2.jpg', 'En Adopción'),
(9, 'Colo', 'Gato', 'Naranjoso', NULL, 'Macho', 'Descripción pendiente', 'uploads/68fe4875cf6c2-gatos-naranjas.jpg', 'Perdido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avistamientos`
--

CREATE TABLE `avistamientos` (
  `id_avistamiento` int(11) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `imagen_url` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_avistamiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario_reporta` int(11) DEFAULT NULL COMMENT 'FK a usuarios, si el usuario está logueado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id_mensaje` int(11) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `contenido` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de envío del mensaje (automática)',
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id_mensaje`, `id_remitente`, `id_destinatario`, `asunto`, `contenido`, `fecha_envio`, `leido`) VALUES
(1, 2, 1, 'Interés en la publicación: Perrito Buscando Hogar (Cartucho)', 'Hola me interesa', '2025-10-17 12:24:33', 0),
(2, 3, 1, 'Interés en la publicación: Perrito busca hogar temporal (Teo)', 'Hola le puedo dar hogar temporal', '2025-10-17 12:56:02', 0),
(3, 1, 2, 'Re: Interés en la publicación: Perrito Buscando Hogar (Cartucho)', 'Hola si contactame a @fnb_dll', '2025-10-17 14:03:02', 0),
(4, 2, 3, 'Interés en la publicación: Se busca familia responsable (ramoncito)', 'hola quiero al perro', '2025-10-26 16:05:31', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id_publicacion` int(11) NOT NULL,
  `id_animal` int(11) NOT NULL,
  `id_usuario_publicador` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL COMMENT 'Título de la publicación',
  `contenido` text NOT NULL COMMENT 'Contenido detallado de la publicación',
  `tipo_publicacion` enum('Adopción','Perdido','Encontrado','Hogar Temporal') NOT NULL COMMENT 'Tipo de publicación',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora en que se publicó el animal (automática)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id_publicacion`, `id_animal`, `id_usuario_publicador`, `titulo`, `contenido`, `tipo_publicacion`, `fecha_publicacion`) VALUES
(1, 1, 1, 'Perrito Buscando Hogar', 'Es un perro energico y nesesita salir a pasear', 'Adopción', '2025-10-17 12:10:35'),
(2, 2, 1, 'Gato Perdido', 'Se vio un gato perdido', 'Perdido', '2025-10-17 12:14:09'),
(3, 3, 1, 'Perrito busca hogar temporal', 'Encontre un perro caniche y no lo puedo tener mucho tiempo', 'Hogar Temporal', '2025-10-17 12:22:50'),
(5, 5, 1, 'Se busca adopcion responsable', 'Dalma busca casa perrita amorosa', 'Adopción', '2025-10-17 14:18:26'),
(6, 6, 1, 'Gato Busca casa', 'Sofi busca hogar es una gata estudiosa', 'Adopción', '2025-10-17 14:19:17'),
(7, 7, 2, 'Caniche perdido', 'se perdio el lunes 13/10/2025', 'Perdido', '2025-10-17 14:21:29'),
(8, 8, 3, 'Se busca familia responsable', 'Somos un refugio de animales, buscamos adopcion responsable para ramoncito', 'Adopción', '2025-10-17 14:22:50'),
(9, 9, 2, 'Gato perdido', 'Se perdio ayer por la tarde en villa luzuriaga', 'Perdido', '2025-10-26 16:12:37');
--jeje
(10, 5, 2, 'Dalma busca familia con patio', 'Dalma es muy activa y necesita espacio para correr. Ideal para familia con patio y tiempo para paseos.', 'Adopción', '2025-10-20 10:00:00'),
(11, 6, 1, 'Sofi necesita hogar temporal por vacaciones', 'Busco hogar temporal por 2 semanas mientras viajo. Sofi es tranquila y está esterilizada.', 'Hogar Temporal', '2025-10-21 09:30:00'),
(12, 8, 3, 'Ramoncito busca familia responsable (refugio)', 'Ramoncito está en excelente estado de salud, se entrega con vacunas al día y control veterinario.', 'Adopción', '2025-10-22 08:45:00'),
(13, 2, 2, 'Gato encontrado en barrio centro', 'Se encontró un gato cerca de la plaza. Tiene collar azul pero sin identificación. Busca su hogar.', 'Encontrado', '2025-10-23 19:15:00'),
(14, 9, 1, 'Colo perdido en Villa Luzuriaga', 'Colo se perdió ayer por la tarde. Responde al nombre y es muy manso. Recompensa pequeña.', 'Perdido', '2025-10-26 16:20:00'),
(15, 1, 3, 'Cartucho listo para adopción definitiva', 'Cartucho es muy cariñoso, sociable con otros perros y ya está listo para encontrar su familia.', 'Adopción', '2025-10-27 11:00:00');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_perdidos`
--

CREATE TABLE `reportes_perdidos` (
  `id_reporte` int(11) NOT NULL,
  `id_animal` int(11) NOT NULL COMMENT 'Referencia al animal perdido',
  `id_usuario_reportador` int(11) DEFAULT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora en que se generó el reporte (automática)',
  `ultima_ubicacion_vista` varchar(255) NOT NULL COMMENT 'Calle, barrio o coordenadas donde fue visto por última vez.',
  `recompensa` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto de recompensa ofrecido (opcional).',
  `caracteristicas_distintivas` text DEFAULT NULL COMMENT 'Descripción detallada de señales, cicatrices, collar, etc.',
  `latitud` decimal(10,8) DEFAULT NULL COMMENT 'Latitud de la última ubicación vista',
  `longitud` decimal(11,8) DEFAULT NULL COMMENT 'Longitud de la última ubicación vista'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `reportes_perdidos`
--

INSERT INTO `reportes_perdidos` (`id_reporte`, `id_animal`, `id_usuario_reportador`, `fecha_reporte`, `ultima_ubicacion_vista`, `recompensa`, `caracteristicas_distintivas`, `latitud`, `longitud`) VALUES
(1, 7, 2, '2025-10-17 14:21:29', 'Calle berna esquina remedios de escalada , villa luzuriaga', 0.00, '', NULL, NULL),
(2, 9, 2, '2025-10-26 16:12:37', 'Ubicación aproximada: -34.6816, -58.6077', 0.00, 'Mancha en el ojo ', -34.68159550, -58.60770470);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `dni` varchar(15) NOT NULL COMMENT 'Documento Nacional de Identidad, debe ser único',
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Hash de la contraseña del usuario (usar bcrypt o Argon2)',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` timestamp NULL DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `es_refugio` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'TRUE si es un refugio, FALSE si es particular',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 si es super admin, 0 si no lo es',
  `descripcion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de registro de la cuenta (automática)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `dni`, `nombre`, `email`, `password_hash`, `reset_token`, `token_expiry`, `telefono`, `direccion`, `es_refugio`, `is_admin`, `descripcion`, `fecha_registro`) VALUES
(1, '43506695', 'Facundo Bustos', 'fnbustos2001@gmail.com', '$2y$10$7l7/1BTrZHRE5/sgFd4RmurTIW383x2SkHl7dxm64gQvFOHnxSAoG', NULL, NULL, '', NULL, 0, 0, NULL, '2025-10-17 12:08:30'),
(2, '200000CD', 'Nicolas', 'aimilanesa@gmail.com', '$2y$10$MLIW8uI8s7m8p6HxviP4i.CBo.34CBYz5/CWijBu7QBobl8sR94zy', NULL, NULL, '', NULL, 0, 1, NULL, '2025-10-17 12:24:10'),
(3, '45608777', 'Refugio Esperanza', 'nismanrenacido@gmail.com', '$2y$10$tRAmx3aGJQEfhUw5TsPVnukfMZx1.g9kh1L7Cmx.WokeAg0w1pkEi', NULL, NULL, '1168902781', NULL, 1, 0, NULL, '2025-10-17 12:44:18');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adopciones`
--
ALTER TABLE `adopciones`
  ADD PRIMARY KEY (`id_adopcion`),
  ADD UNIQUE KEY `id_animal` (`id_animal`),
  ADD KEY `id_usuario_adoptante` (`id_usuario_adoptante`);

--
-- Indices de la tabla `animales`
--
ALTER TABLE `animales`
  ADD PRIMARY KEY (`id_animal`),
  ADD KEY `idx_animales_estado` (`estado`),
  ADD KEY `idx_animales_especie` (`especie`);

--
-- Indices de la tabla `avistamientos`
--
ALTER TABLE `avistamientos`
  ADD PRIMARY KEY (`id_avistamiento`),
  ADD KEY `idx_avistamientos_fecha` (`fecha_avistamiento`),
  ADD KEY `fk_avistamiento_usuario` (`id_usuario_reporta`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_remitente` (`id_remitente`),
  ADD KEY `id_destinatario` (`id_destinatario`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id_publicacion`),
  ADD KEY `id_animal` (`id_animal`),
  ADD KEY `id_usuario_publicador` (`id_usuario_publicador`);

--
-- Indices de la tabla `reportes_perdidos`
--
ALTER TABLE `reportes_perdidos`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `id_usuario_reportador` (`id_usuario_reportador`),
  ADD KEY `idx_reportes_animal` (`id_animal`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `idx_usuarios_es_refugio` (`es_refugio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `adopciones`
--
ALTER TABLE `adopciones`
  MODIFY `id_adopcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `animales`
--
ALTER TABLE `animales`
  MODIFY `id_animal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `avistamientos`
--
ALTER TABLE `avistamientos`
  MODIFY `id_avistamiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_publicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `reportes_perdidos`
--
ALTER TABLE `reportes_perdidos`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adopciones`
--
ALTER TABLE `adopciones`
  ADD CONSTRAINT `adopciones_ibfk_1` FOREIGN KEY (`id_animal`) REFERENCES `animales` (`id_animal`),
  ADD CONSTRAINT `adopciones_ibfk_2` FOREIGN KEY (`id_usuario_adoptante`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `avistamientos`
--
ALTER TABLE `avistamientos`
  ADD CONSTRAINT `fk_avistamiento_usuario` FOREIGN KEY (`id_usuario_reporta`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`id_animal`) REFERENCES `animales` (`id_animal`) ON DELETE CASCADE,
  ADD CONSTRAINT `publicaciones_ibfk_2` FOREIGN KEY (`id_usuario_publicador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes_perdidos`
--
ALTER TABLE `reportes_perdidos`
  ADD CONSTRAINT `reportes_perdidos_ibfk_1` FOREIGN KEY (`id_animal`) REFERENCES `animales` (`id_animal`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_perdidos_ibfk_2` FOREIGN KEY (`id_usuario_reportador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
