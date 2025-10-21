-- Agregar columnas para verificación de email y recuperación de contraseña
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS verification_code VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS verification_code_expires TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS reset_code VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reset_code_expires TIMESTAMP NULL;

-- Actualizar columna existente si es necesario
ALTER TABLE users MODIFY COLUMN reset_code VARCHAR(255) NULL;

-- Índices para optimizar búsquedas
ALTER TABLE users 
ADD INDEX IF NOT EXISTS idx_verification_code (verification_code),
ADD INDEX IF NOT EXISTS idx_reset_code (reset_code);