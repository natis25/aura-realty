-- ============================================================
-- 🏡 BASE DE DATOS PARA SISTEMA INMOBILIARIO (ESCALABLE)
-- ============================================================

CREATE DATABASE IF NOT EXISTS inmobiliaria
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE inmobiliaria;

-- ============================================================
-- 1️⃣ TABLA DE ROLES
-- ============================================================

CREATE TABLE roles (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(50) NOT NULL UNIQUE COMMENT 'admin, cliente, agente'
);

INSERT INTO roles (nombre) VALUES
('admin'),
('cliente'),
('agente');

-- ============================================================
-- 2️⃣ TABLA DE USUARIOS (GENÉRICA CON ROLES)
-- ============================================================

CREATE TABLE usuarios (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(150) NOT NULL,
correo VARCHAR(150) NOT NULL UNIQUE,
telefono VARCHAR(30),
contrasena VARCHAR(255) NOT NULL,
rol_id INT NOT NULL,
estado ENUM('activo','inactivo') DEFAULT 'activo',
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Datos de prueba
INSERT INTO usuarios (nombre, correo, telefono, contrasena, rol_id) VALUES
('Administrador General', '[admin@demo.com](mailto:admin@demo.com)', '70000000', '123456', 1),
('Carlos Agent',        '[carlos@inmo.com](mailto:carlos@inmo.com)', '70000001', '123456', 3),
('María Agent',         '[maria@inmo.com](mailto:maria@inmo.com)',  '70000002', '123456', 3),
('Juan Pérez',          '[juan@gmail.com](mailto:juan@gmail.com)',  '70000003', '123456', 2),
('Ana López',           '[ana@gmail.com](mailto:ana@gmail.com)',   '70000004', '123456', 2);

-- ============================================================
-- 3️⃣ TABLA DE AGENTES (INFORMACIÓN ESPECÍFICA)
-- ============================================================

CREATE TABLE agentes (
id INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT NOT NULL UNIQUE,
disponible BOOLEAN DEFAULT TRUE,
especialidad VARCHAR(100),
ubicacion VARCHAR(150),
FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Datos de prueba
INSERT INTO agentes (usuario_id, disponible, especialidad, ubicacion) VALUES
(2, TRUE, 'Ventas',     'La Paz'),
(3, TRUE, 'Alquileres', 'El Alto');

-- ============================================================
-- 4️⃣ TABLA DE PROPIEDADES
-- ============================================================

CREATE TABLE propiedades (
id INT AUTO_INCREMENT PRIMARY KEY,
titulo VARCHAR(150) NOT NULL,
direccion VARCHAR(255),
ciudad VARCHAR(100),
tipo ENUM('venta','alquiler') DEFAULT 'venta',
precio DECIMAL(12,2),
area DECIMAL(10,2),
habitaciones INT,
banos INT,
descripcion TEXT,
imagen_principal VARCHAR(255),
disponible TINYINT(1) DEFAULT 1,
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Datos de prueba
INSERT INTO propiedades
(titulo, direccion, ciudad, tipo, precio, area, habitaciones, banos, descripcion, imagen_principal) VALUES
('Departamento en Sopocachi', 'Calle 6 #123', 'La Paz', 'venta',   85000, 95, 3, 2, 'Hermoso departamento cercano al centro.', 'img1.jpg'),
('Casa en Achumani',          'Av. 3 #45',    'La Paz', 'venta',  190000, 210, 4, 3, 'Casa amplia con jardín.',               'img2.jpg'),
('Garzonier en Miraflores',   'Calle Landaeta','La Paz','alquiler', 350, 45, 1, 1, 'Garzonier moderno y amoblado.',         'img3.jpg');

-- ============================================================
-- 5️⃣ DISPONIBILIDAD REAL DE AGENTES (CALENDARIO)
-- ============================================================

CREATE TABLE disponibilidad_agente (
id INT AUTO_INCREMENT PRIMARY KEY,
agente_id INT NOT NULL,
fecha DATE NOT NULL,
hora_inicio TIME NOT NULL,
hora_fin TIME NOT NULL,
disponible BOOLEAN DEFAULT TRUE,
FOREIGN KEY (agente_id) REFERENCES agentes(id)
);

-- Índice para optimizar búsquedas por fecha/hora
CREATE INDEX idx_disponibilidad_agente_fecha
ON disponibilidad_agente(agente_id, fecha, hora_inicio, hora_fin);

-- Datos de prueba
INSERT INTO disponibilidad_agente (agente_id, fecha, hora_inicio, hora_fin) VALUES
(1, '2025-11-30', '09:00:00', '12:00:00'),
(2, '2025-11-30', '13:00:00', '17:00:00');

-- ============================================================
-- 6️⃣ SOLICITUDES DE CITA (CLIENTE → SISTEMA)
-- ============================================================

CREATE TABLE solicitudes_cita (
id INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT NOT NULL,
propiedad_id INT NOT NULL,
fecha_solicitada DATE NOT NULL,
hora_solicitada TIME NOT NULL,
estado ENUM('pendiente','aceptada','rechazada','cancelada','completada','en_progreso') DEFAULT 'pendiente',
mensaje TEXT,
creada_por ENUM('cliente','admin') DEFAULT 'cliente',
agente_asignado INT,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
FOREIGN KEY (propiedad_id) REFERENCES propiedades(id),
FOREIGN KEY (agente_asignado) REFERENCES agentes(id)
);

-- Índice para evitar doble reserva de la misma propiedad (backend también debe validar choques de hora)
CREATE UNIQUE INDEX idx_solicitud_unica
ON solicitudes_cita(propiedad_id, fecha_solicitada, hora_solicitada);

-- Datos de prueba
INSERT INTO solicitudes_cita
(usuario_id, propiedad_id, fecha_solicitada, hora_solicitada, estado, mensaje, agente_asignado) VALUES
(4, 1, '2025-11-30', '10:00:00', 'pendiente', 'Quiero ver el departamento.', 1),
(5, 3, '2025-11-30', '14:00:00', 'aceptada',  'Consulta por alquiler.',       2);

-- ============================================================
-- 7️⃣ CITAS CONFIRMADAS (AGENDA REAL)
-- ============================================================

CREATE TABLE citas (
id INT AUTO_INCREMENT PRIMARY KEY,
solicitud_id INT NOT NULL,
agente_id INT NOT NULL,
fecha DATE NOT NULL,
hora TIME NOT NULL,
estado ENUM('programada','en_progreso','finalizada','cancelada') DEFAULT 'programada',
nota TEXT,
FOREIGN KEY (solicitud_id) REFERENCES solicitudes_cita(id) ON DELETE CASCADE,
FOREIGN KEY (agente_id) REFERENCES agentes(id)
);

-- Datos de prueba
INSERT INTO citas (solicitud_id, agente_id, fecha, hora, estado) VALUES
(2, 2, '2025-11-30', '14:00:00', 'programada');

-- ============================================================
-- 8️⃣ NOTIFICACIONES
-- ============================================================

CREATE TABLE notificaciones (
id INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT NOT NULL,
titulo VARCHAR(150),
mensaje TEXT NOT NULL,
leida TINYINT(1) DEFAULT 0,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Datos de prueba
INSERT INTO notificaciones (usuario_id, titulo, mensaje) VALUES
(4, 'Solicitud recibida', 'Tu solicitud para visitar la propiedad fue registrada.'),
(5, 'Cita confirmada',   'Tu cita para el garzonier fue confirmada.');

-- ============================================================
-- 9️⃣ REGISTRO DE LOGS (AUDITORÍA)
-- ============================================================

CREATE TABLE logs (
id INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT,
accion VARCHAR(255),
detalle TEXT,
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Datos de prueba
INSERT INTO logs (usuario_id, accion, detalle) VALUES
(1, 'CREAR_PROPIEDAD', 'Se agregó la propiedad de Achumani.'),
(2, 'ACEPTAR_SOLICITUD', 'El agente Carlos aceptó una solicitud.');



UPDATE usuarios SET correo = 'admin@demo.com' WHERE id = 1;
UPDATE usuarios SET correo = 'carlos@inmo.com' WHERE id = 2;
UPDATE usuarios SET correo = 'maria@inmo.com' WHERE id = 3;
UPDATE usuarios SET correo = 'juan@gmail.com' WHERE id = 4;
UPDATE usuarios SET correo = 'ana@gmail.com' WHERE id = 5;

--ampliar la tabla de users
ALTER TABLE usuarios
ADD estado ENUM('activo','pendiente','inactivo') DEFAULT 'activo';

--columnas de reset de contraseña
ALTER TABLE usuarios
ADD reset_token VARCHAR(255) NULL,
ADD reset_expires_at DATETIME NULL,
ADD requiere_reset_password TINYINT(1) DEFAULT 0;
--prueba para hash
UPDATE usuarios
SET contrasena = '$2y$10$8k8zQ9J3kYyFvQz7Jtq4VOPkF7B3u3bN9c0ZK7Xz7uK6l1s9q'
WHERE contrasena IS NOT NULL;

--mas columnas para usuer
ALTER TABLE usuarios
ADD direccion VARCHAR(255) NULL,
ADD ciudad VARCHAR(100) NULL,
ADD documento_identidad VARCHAR(50) NULL,
ADD fecha_nacimiento DATE NULL;


