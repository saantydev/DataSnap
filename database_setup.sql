-- Script para crear la tabla archivos si no existe
-- Ejecutar este script en la base de datos 'datasnap'

CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    tamano BIGINT DEFAULT 0,
    estado ENUM('original', 'optimizado', 'borrado', 'pendiente') DEFAULT 'pendiente',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_optimizacion TIMESTAMP NULL,
    ruta_optimizada VARCHAR(500) NULL,
    drive_id_original VARCHAR(255) NULL,
    drive_link_original TEXT NULL,
    drive_id_optimizado VARCHAR(255) NULL,
    drive_link_optimizado TEXT NULL,
    pending_timestamp TIMESTAMP NULL,
    INDEX idx_estado (estado),
    INDEX idx_user_id (user_id),
    INDEX idx_fecha_subida (fecha_subida)
);

-- Agregar columnas faltantes si la tabla ya existe
ALTER TABLE archivos 
ADD COLUMN IF NOT EXISTS nombre VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS tamano BIGINT DEFAULT 0,
ADD COLUMN IF NOT EXISTS fecha_optimizacion TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS ruta_optimizada VARCHAR(500) NULL,
ADD COLUMN IF NOT EXISTS drive_id_original VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS drive_link_original TEXT NULL,
ADD COLUMN IF NOT EXISTS drive_id_optimizado VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS drive_link_optimizado TEXT NULL;

-- Verificar que la tabla se creó correctamente
DESCRIBE archivos;