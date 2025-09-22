-- Script para crear la tabla archivos si no existe
-- Ejecutar este script en la base de datos 'datasnap'

CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    estado ENUM('original', 'optimizado', 'borrado', 'pendiente') DEFAULT 'pendiente',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_user_id (user_id),
    INDEX idx_fecha_subida (fecha_subida)
);

-- Verificar que la tabla se creó correctamente
DESCRIBE archivos;