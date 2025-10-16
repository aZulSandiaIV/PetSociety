USE petsociety;

-- Usuarios
INSERT INTO usuarios (dni, nombre, email, password_hash, telefono, direccion, es_refugio, descripcion)
VALUES
('12345678', 'Juan Pérez', 'juan@mail.com', '$2y$10$hash1', '1122334455', 'Calle Falsa 123', 0, 'Amante de los animales'),
('87654321', 'Refugio Patitas', 'refugio@patitas.com', '$2y$10$hash2', '1133445566', 'Av. Siempreviva 742', 1, 'Refugio de mascotas'),
('11223344', 'Ana Gómez', 'ana@mail.com', '$2y$10$hash3', '1144556677', 'Calle Luna 456', 0, NULL);

-- Animales
INSERT INTO animales (id_animal, nombre, especie, raza, edad, genero, descripcion, estado)
VALUES
(1, 'Firulais', 'Perro', 'Labrador', 3, 'Macho', 'Perro juguetón y amigable', 'En Adopción'),
(2, 'Mishi', 'Gato', 'Siames', 2, 'Hembra', 'Gata tranquila y cariñosa', 'Perdido'),
(3, 'Rocky', 'Perro', 'Mestizo', 5, 'Macho', 'Perro rescatado, busca hogar', 'Hogar Temporal');

-- Publicaciones
INSERT INTO publicaciones (id_animal, id_usuario_publicador, titulo, contenido, tipo_publicacion)
VALUES
(1, 1, '¡Firulais busca familia!', 'Firulais es un labrador muy bueno, ideal para niños.', 'Adopción'),
(2, 3, 'Se perdió Mishi', 'Mishi desapareció en el barrio Centro. Ayuda por favor.', 'Perdido'),
(3, 2, 'Rocky en hogar temporal', 'Rocky está en nuestro refugio esperando adopción.', 'Hogar Temporal');

-- Reportes Perdidos
INSERT INTO reportes_perdidos (id_animal, id_usuario_reportador, ultima_ubicacion_vista, recompensa, caracteristicas_distintivas)
VALUES
(2, 3, 'Barrio Centro, cerca de la plaza', 500.00, 'Collar rojo, cicatriz en la oreja izquierda');

-- Adopciones
INSERT INTO adopciones (id_animal, id_usuario_adoptante, fecha_adopcion, notas)
VALUES
(1, 1, '2025-10-01', 'Adopción realizada con éxito');

-- Mensajes
INSERT INTO mensajes (id_remitente, id_destinatario, asunto, contenido)
VALUES
(1, 2, 'Consulta por adopción', 'Hola, estoy interesado en adoptar a Firulais.'),
(2, 1, 'Respuesta sobre Firulais', '¡Hola! Firulais sigue disponible, ¿quieres conocerlo?');