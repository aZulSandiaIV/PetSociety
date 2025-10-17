create database if not exists `petsociety` default character set utf8mb4 collate utf8mb4_spanish_ci;
use `petsociety`;
DELETE FROM mensajes;
DELETE FROM publicaciones;
DELETE FROM reportes_perdidos;
DELETE FROM adopciones;
DELETE FROM animales;
DELETE FROM usuarios;

ALTER TABLE usuarios AUTO_INCREMENT = 1;
ALTER TABLE animales AUTO_INCREMENT = 1;
ALTER TABLE publicaciones AUTO_INCREMENT = 1;
ALTER TABLE reportes_perdidos AUTO_INCREMENT = 1;
ALTER TABLE adopciones AUTO_INCREMENT = 1;
ALTER TABLE mensajes AUTO_INCREMENT = 1;