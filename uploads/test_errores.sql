CREATE DATABASE tienda_test;
USE tienda_test;

CREATE TABLE usuarios (
    id INT PRIMARI KEY AUTOINCREMENT,
    nombre VARCHARR(100),
    email EMAIL NOT NUL,
    password VARCHAR(50),
    telefono VARCHAR(20),
    fecha_registro TIMESTAP DEFAULT CURREN_TIMESTAMP
);

INSERT INTO usuarios (nombre, email, password, telefono, fecha_registro) VALUES
('juan perez', 'juan@gmai.com', '123456', '91234567', '2023-01-15'),
('MARIA GARCIA', 'maria@hotmial.com', 'password', '+34-987-654-321', '2023-02-20'),
('Pedro López', 'pedro@yahoo.co', 'abc123', '123456789', '2023-03-10'),
('', 'ana@gmail.com', '', '654321987', '2023-04-05'),
('Carlos Ruiz', 'carlos', 'carlos123', '91-111-2222', '2023-05-12'),
('Sofia Martinez', 'sofia@outlok.com', 'sofia456', '', '2023-06-18'),
('Luis Fernandez', 'luis@gmail.com', 'luis789', '91-555-4444', '2023-07-22'),
('Ana Torres', 'ana@gmail.com', 'ana321', '654321987', '2023-08-30'),
('Luis Fernandez', 'luis@gmail.com', 'luis789', '91-555-4444', '2023-07-22');

CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    precio FLOAT,
    stock INTEGER,
    categoria VARCHAR(50),
    activo BOOL DEFAULT TRUE
);

INSERT INTO productos (nombre, precio, stock, categoria, activo) VALUES
('laptop hp', 850.50, 10, 'informatica', 1),
('MOUSE LOGITECH', 25.99, '', 'accesorios', TRUE),
('Teclado Mecánico', 'abc', 25, 'accesorios', 'si'),
('', 199.99, 5, 'monitores', 1),
('Auriculares Sony', 75.00, -5, 'audio', FALSE),
('Webcam HD', 45.50, 30, '', 'activo'),
('Disco SSD 1TB', 120.00, 15, 'almacenamiento', 1),
('Mouse Logitech', 25.99, 50, 'accesorios', TRUE);