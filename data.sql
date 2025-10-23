-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `abm-propiedades` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `abm-propiedades`;

-- Crear tabla propiedades
CREATE TABLE IF NOT EXISTS propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(12,2) NOT NULL,
    zona VARCHAR(100) NOT NULL,
    tipo_vivienda VARCHAR(50) NOT NULL,
    tipo_contrato VARCHAR(50) NOT NULL,
    direccion TEXT NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de ejemplo
INSERT INTO propiedades (nombre, precio, zona, tipo_vivienda, tipo_contrato, direccion, imagen) VALUES
('Casa Moderna', 250000.00, 'Norte', 'Casa', 'Venta', 'Av. Principal 123', 'casa1.jpg'),
('Departamento Céntrico', 1500.00, 'Centro', 'Departamento', 'Alquiler', 'Calle Secundaria 456', 'depto1.jpg'),
('PH Acogedor', 180000.00, 'Sur', 'PH', 'Venta', 'Pasaje Residencial 789', 'ph1.jpg');