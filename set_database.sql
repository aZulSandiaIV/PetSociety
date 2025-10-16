-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-10-2025 a las 18:29:45
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

create database if not exists `petsociety` default character set utf8mb4 collate utf8mb4_spanish_ci;
use `petsociety`;

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
  `estado` enum('En Adopción','Perdido','Adoptado','Hogar Temporal') NOT NULL COMMENT 'Estado actual del animal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  `caracteristicas_distintivas` text DEFAULT NULL COMMENT 'Descripción detallada de señales, cicatrices, collar, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  `descripcion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de registro de la cuenta (automática)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  ADD UNIQUE KEY `id_animal` (`id_animal`),
  ADD KEY `id_usuario_reportador` (`id_usuario_reportador`);

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
  MODIFY `id_adopcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `animales`
--
ALTER TABLE `animales`
  MODIFY `id_animal` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_publicacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_perdidos`
--
ALTER TABLE `reportes_perdidos`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

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
