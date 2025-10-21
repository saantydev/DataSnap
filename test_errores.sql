-- Archivo SQL con errores para probar DataSnap
-- Este archivo contiene errores sintácticos, duplicados y problemas de estructura

CREATE TABLE usuarios (
    id INT PRIMARY KEY,
    nombre VARCHAR(50),
    email VARCHAR(100),
    edad INT,
    ciudad VARCHAR(50)
);

-- Error: Punto y coma faltante
INSERT INTO usuarios VALUES (1, 'Juan Pérez', 'juan@email.com', 25, 'Madrid')

-- Datos duplicados
INSERT INTO usuarios VALUES (1, 'Juan Pérez', 'juan@email.com', 25, 'Madrid');
INSERT INTO usuarios VALUES (1, 'Juan Pérez Duplicado', 'juan2@email.com', 26, 'Barcelona');

-- Error: Comillas mal cerradas
INSERT INTO usuarios VALUES (2, 'María García', 'maria@email.com, 30, 'Sevilla');

-- Espacios extra y formato inconsistente
INSERT INTO usuarios VALUES (   3   ,    'Pedro López'   ,   'pedro@email.com'  ,  28  ,   'Valencia'   );

-- Error: Tipo de dato incorrecto
INSERT INTO usuarios VALUES (4, 'Ana Martín', 'ana@email.com', 'treinta y dos', 'Bilbao');

-- Datos con caracteres especiales problemáticos
INSERT INTO usuarios VALUES (5, 'José María O'Connor', 'jose@email.com', 35, 'A Coruña');

-- Error: Campo faltante
INSERT INTO usuarios (id, nombre, email, edad) VALUES (6, 'Laura Sánchez', 'laura@email.com', 29);

-- Duplicado con diferente capitalización
INSERT INTO usuarios VALUES (7, 'JUAN PÉREZ', 'JUAN@EMAIL.COM', 25, 'MADRID');

-- Error: Sintaxis SQL incorrecta
INSRT INTO usuarios VALUES (8, 'Carlos Ruiz', 'carlos@email.com', 33, 'Zaragoza');

-- Líneas vacías y comentarios mal formateados


/* Comentario de bloque mal cerrado
INSERT INTO usuarios VALUES (9, 'Elena Moreno', 'elena@email.com', 27, 'Málaga');

-- Más datos con problemas
INSERT INTO usuarios VALUES (10, '', 'vacio@email.com', 0, '');
INSERT INTO usuarios VALUES (11, NULL, NULL, NULL, NULL);

-- Error: Tabla inexistente
INSERT INTO clientes VALUES (1, 'Cliente Test');

-- Datos con formato de fecha incorrecto
CREATE TABLE pedidos (
    id INT,
    fecha DATE,
    usuario_id INT
);

INSERT INTO pedidos VALUES (1, '2024/13/45', 1);
INSERT INTO pedidos VALUES (2, 'ayer', 2);

-- Error: Constraint violation
ALTER TABLE usuarios ADD CONSTRAINT unique_email UNIQUE (email);
INSERT INTO usuarios VALUES (12, 'Repetido', 'juan@email.com', 40, 'Córdoba');

-- Consulta con error de sintaxis
SELCT * FROM usuarios WHERE edad > 30;

-- Datos con encoding problemático
INSERT INTO usuarios VALUES (13, 'Ñoño Muñoz', 'nono@email.com', 45, 'Logroño');