-- Crear base de datos
CREATE DATABASE IF NOT EXISTS unimanager;
USE unimanager;

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    ci VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    semestre INT DEFAULT 1,
    estado ENUM('active', 'inactive', 'graduated', 'suspended') DEFAULT 'active',
    promedio DECIMAL(5, 2) DEFAULT 0.00,
    notas JSON,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ci (ci),
    INDEX idx_estado (estado),
    INDEX idx_semestre (semestre),
    INDEX idx_promedio (promedio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de prueba
INSERT INTO estudiantes (codigo, nombre, apellido_paterno, apellido_materno, email, ci, telefono, semestre, estado, promedio) VALUES
('2024-00001', 'Juan', 'Pérez', 'García', 'juan.perez@est.edu.bo', '12345678-LP', '+59178451234', 3, 'active', 85.50),
('2024-00002', 'María', 'López', 'Quispe', 'maria.lopez@est.edu.bo', '23456789-SC', '+59176542345', 4, 'active', 92.75),
('2024-00003', 'Carlos', 'Mamani', 'Condori', 'carlos.mamani@est.edu.bo', '34567890-CB', '+59172345678', 2, 'active', 78.25),
('2024-00004', 'Ana', 'García', 'Torres', 'ana.garcia@est.edu.bo', '45678901-LP', NULL, 5, 'inactive', 55.00),
('2024-00005', 'Luis', 'Ruiz', 'Apaza', 'luis.ruiz@est.edu.bo', '56789012-SC', '+59178234567', 3, 'active', 88.00);
