-- Crear la base de datos principal
CREATE DATABASE IF NOT EXISTS citas;
USE citas;

-- Tabla de roles
CREATE TABLE roles (
    idRol INT PRIMARY KEY AUTO_INCREMENT,
    nombreRol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    fechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    idUsuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    idRol INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fechaActualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idRol) REFERENCES roles(idRol)
);

-- Tabla de tipos de propiedad
CREATE TABLE tipos_propiedad (
    idTipoPropiedad INT PRIMARY KEY AUTO_INCREMENT,
    nombreTipo VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);

-- Tabla de propiedades (combinando tu estructura personalizada)
CREATE TABLE propiedades (
    idPropiedad INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(50) NOT NULL, -- casa, departamento, local, etc.
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    precio DECIMAL(12,2) NOT NULL,
    habitaciones INT,
    banos INT,
    metros_cuadrados INT,
    idUsuario INT,
    disponible BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)
);

-- Tabla de estados de cita
CREATE TABLE estados_cita (
    idEstado INT PRIMARY KEY AUTO_INCREMENT,
    nombreEstado VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
);

-- Tabla de citas
CREATE TABLE citas (
    idCita INT PRIMARY KEY AUTO_INCREMENT,
    idPropiedad INT NOT NULL,
    fechaVisita DATE NOT NULL,
    horaInicio TIME NOT NULL,
    horaFin TIME NOT NULL,
    nombreCliente VARCHAR(150) NOT NULL,
    telefonoCliente VARCHAR(20) NOT NULL,
    correoCliente VARCHAR(150) NOT NULL,
    idEstado INT NOT NULL DEFAULT 1,
    notas TEXT,
    idUsuario INT,
    fechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fechaActualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idPropiedad) REFERENCES propiedades(idPropiedad),
    FOREIGN KEY (idEstado) REFERENCES estados_cita(idEstado),
    FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)
);

-- Tabla historial
CREATE TABLE historial_citas (
    idHistorial INT PRIMARY KEY AUTO_INCREMENT,
    idCita INT NOT NULL,
    idEstadoAnterior INT,
    idEstadoNuevo INT NOT NULL,
    motivoCambio TEXT,
    idUsuario INT NOT NULL,
    fechaCambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idCita) REFERENCES citas(idCita),
    FOREIGN KEY (idEstadoAnterior) REFERENCES estados_cita(idEstado),
    FOREIGN KEY (idEstadoNuevo) REFERENCES estados_cita(idEstado),
    FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)
);

-- Insertar datos base
INSERT INTO roles (nombreRol, descripcion) VALUES 
('Administrador', 'Acceso completo al sistema'),
('Agente', 'Gestiona propiedades y citas'),
('Cliente', 'Puede solicitar citas');

INSERT INTO estados_cita (nombreEstado, descripcion) VALUES 
('Pendiente', 'Cita pendiente de confirmación'),
('Confirmada', 'Cita confirmada por el agente'),
('Completada', 'Visita realizada'),
('Cancelada', 'Cita cancelada');

-- Insertar usuarios
INSERT INTO usuarios (nombre, email, password, telefono, idRol) VALUES 
('Administrador Principal', 'admin@citas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '777-0000', 1),
('María González', 'maria@citas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '777-1111', 2),
('Carlos Rodríguez', 'carlos@citas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '777-2222', 2);

-- Insertar propiedades
INSERT INTO propiedades (titulo, descripcion, tipo, direccion, ciudad, precio, habitaciones, banos, metros_cuadrados, idUsuario)
VALUES
('Casa familiar con jardín', 'Casa cómoda con patio y garaje', 'Casa', 'Av. Central 101', 'Ciudad Central', 180000.00, 3, 2, 150, 2),
('Departamento moderno', 'Condominio en el centro con gimnasio y piscina', 'Departamento', 'Calle Sur 23', 'Ciudad Central', 220000.00, 2, 2, 95, 3),
('Local comercial amplio', 'Espacio comercial ideal para tienda', 'Local', 'Zona Norte 45', 'Ciudad Central', 150000.00, 0, 1, 80, 2);

-- Insertar citas de ejemplo
INSERT INTO citas (idPropiedad, fechaVisita, horaInicio, horaFin, nombreCliente, telefonoCliente, correoCliente, idEstado, notas, idUsuario)
VALUES
(1, '2025-10-21', '10:00:00', '11:00:00', 'Ana Pérez', '777-3333', 'ana@correo.com', 1, 'Interesada en ver el jardín', 2),
(2, '2025-10-22', '15:00:00', '16:00:00', 'Luis Gómez', '777-4444', 'luis@correo.com', 2, 'Desea negociar el precio', 3),
(3, '2025-10-23', '09:30:00', '10:00:00', 'Sofía Morales', '777-5555', 'sofia@correo.com', 3, 'Cliente satisfecho', 2);

-- Procedimiento para cancelar cita
DELIMITER //
CREATE PROCEDURE cancelar_cita(
    IN p_idCita INT,
    IN p_motivo TEXT,
    IN p_idUsuario INT
)
BEGIN
    DECLARE v_estado_actual INT;
    SELECT idEstado INTO v_estado_actual FROM citas WHERE idCita = p_idCita;
    UPDATE citas SET idEstado = 4, fechaActualizacion = CURRENT_TIMESTAMP WHERE idCita = p_idCita;
    INSERT INTO historial_citas (idCita, idEstadoAnterior, idEstadoNuevo, motivoCambio, idUsuario)
    VALUES (p_idCita, v_estado_actual, 4, p_motivo, p_idUsuario);
END//
DELIMITER ;

-- Vista de citas completas
CREATE VIEW vista_citas_completas AS
SELECT 
    c.idCita,
    c.fechaVisita,
    c.horaInicio,
    c.horaFin,
    c.nombreCliente,
    c.telefonoCliente,
    c.correoCliente,
    c.notas,
    ec.nombreEstado AS estado,
    p.titulo AS propiedad_titulo,
    p.tipo AS propiedad_tipo,
    p.ciudad AS propiedad_ciudad,
    p.precio AS propiedad_precio,
    u.nombre AS agente_asignado
FROM citas c
JOIN estados_cita ec ON c.idEstado = ec.idEstado
JOIN propiedades p ON c.idPropiedad = p.idPropiedad
LEFT JOIN usuarios u ON c.idUsuario = u.idUsuario;

-- Vista para citas vigentes
CREATE VIEW vista_citas_vigentes AS
SELECT * FROM vista_citas_completas
WHERE estado IN ('Pendiente', 'Confirmada')
ORDER BY fechaVisita, horaInicio;

-- Función para contar citas por estado
DELIMITER //
CREATE FUNCTION contar_citas_por_estado(p_idEstado INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM citas WHERE idEstado = p_idEstado;
    RETURN total;
END//
DELIMITER ;

-- Más usuarios (agentes)
INSERT INTO usuarios (nombre, email, password, telefono, idRol) VALUES
('Luis Pérez', 'luis@inmobiliaria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0004', 2),
('Sofía Ramírez', 'sofia@inmobiliaria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0005', 2);

-- Más propiedades
INSERT INTO propiedades (titulo, descripcion, tipo, direccion, ciudad, precio, habitaciones, banos, metros_cuadrados, disponible) VALUES
('Departamento céntrico con balcón', 'Departamento moderno, 2 habitaciones, 1 baño, excelente ubicación.', 'Departamento', 'Calle Central 101', 'Ciudad Central', 120000, 2, 1, 75, 1),
('Casa pequeña en suburbios', 'Casa acogedora, 2 habitaciones, 1 baño, jardín pequeño.', 'Casa', 'Av. Secundaria 222', 'Ciudad Central', 90000, 2, 1, 65, 1),
('Local comercial en zona de negocios', 'Ideal para oficina o tienda, 50 m2.', 'Local', 'Calle Comercio 15', 'Ciudad Central', 50000, 0, 1, 50, 1);

USE citas;

-- Insertar más agentes
INSERT INTO usuarios (nombre, email, password, telefono, idRol) VALUES
('Lucía Pérez', 'lucia@inmobiliaria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0004', 2),
('Jorge Martínez', 'jorge@inmobiliaria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0005', 2);

-- Insertar más propiedades disponibles
INSERT INTO propiedades (titulo, descripcion, tipo, direccion, ciudad, precio, habitaciones, banos, metros_cuadrados, idUsuario, disponible) VALUES
('Apartamento céntrico con terraza', 'Apartamento moderno con 2 habitaciones y terraza.', 'Departamento', 'Calle Central 101', 'Ciudad Central', 150000.00, 2, 2, 75, 3, 1),
('Local comercial en avenida principal', 'Local amplio para negocios, excelente ubicación.', 'Local', 'Av. Comercio 202', 'Ciudad Central', 200000.00, NULL, 2, 120, 4, 1),
('Oficina ejecutiva en edificio corporativo', 'Oficina de 50 m² con excelente vista y acceso a sala de reuniones.', 'Oficina', 'Edificio Empresarial 3er Piso', 'Ciudad Central', 95000.00, NULL, 1, 50, 5, 1),
('Casa moderna con jardín y piscina', 'Casa de lujo, 4 habitaciones, 3 baños y piscina privada.', 'Casa', 'Residencial Verde 33', 'Ciudad Central', 350000.00, 4, 3, 200, 2, 1),
('Terreno urbano para construcción', 'Terreno plano ideal para construir vivienda o comercio.', 'Terreno', 'Zona Industrial 44', 'Ciudad Central', 80000.00, NULL, NULL, 500, 3, 1);

-- Verifica que las propiedades estén disponibles
SELECT idPropiedad, titulo, disponible FROM propiedades;

DROP VIEW IF EXISTS vista_citas_completas;

CREATE VIEW vista_citas_completas AS
SELECT 
    c.idCita,
    c.idUsuario,  -- <--- esta línea es nueva
    c.fechaVisita,
    c.horaInicio,
    c.horaFin,
    c.nombreCliente,
    c.telefonoCliente,
    c.correoCliente,
    c.notas,
    ec.nombreEstado AS estado,
    p.titulo AS propiedad_titulo,
    p.direccion AS propiedad_direccion,
    p.ciudad AS propiedad_ciudad,
    p.precio AS propiedad_precio,
    tp.nombreTipo AS propiedad_tipo,
    u.nombre AS agente_asignado,
    u.email AS agente_email
FROM citas c
INNER JOIN estados_cita ec ON c.idEstado = ec.idEstado
INNER JOIN propiedades p ON c.idPropiedad = p.idPropiedad
INNER JOIN tipos_propiedad tp ON p.idTipoPropiedad = tp.idTipoPropiedad
LEFT JOIN usuarios u ON c.idUsuario = u.idUsuario;
