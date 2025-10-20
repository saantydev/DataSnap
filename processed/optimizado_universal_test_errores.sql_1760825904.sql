-- Tabla usuarios optimizada por IA UNIVERSAL
INSERT INTO usuarios (nombre, email, password, telefono, fecha_registro, precio, stock, categoria, activo) VALUES
('Juan Perez', 'juan@gmail.com', '123456', '91234567', '2023-01-15', NULL, NULL, NULL, NULL),
('Maria Garcia', 'maria@hotmail.com', 'password', '+34-987-654-321', '2023-02-20', NULL, NULL, NULL, NULL),
('Pedro López', 'pedro@yahoo.com', 'abc123', '123456789', '2023-03-10', NULL, NULL, NULL, NULL),
(NULL, 'ana@gmail.com', NULL, '654321987', '2023-04-05', NULL, NULL, NULL, NULL),
('Carlos Ruiz', 'carlos', 'carlos123', '91-111-2222', '2023-05-12', NULL, NULL, NULL, NULL),
('Sofia Martinez', 'sofia@outlook.com', 'sofia456', NULL, '2023-06-18', NULL, NULL, NULL, NULL),
('Luis Fernandez', 'luis@gmail.com', 'luis789', '91-555-4444', '2023-07-22', NULL, NULL, NULL, NULL),
('Ana Torres', 'ana@gmail.com', 'ana321', '654321987', '2023-08-30', NULL, NULL, NULL, NULL);

-- Tabla productos optimizada por IA UNIVERSAL
INSERT INTO productos (nombre, email, password, telefono, fecha_registro, precio, stock, categoria, activo) VALUES
('Laptop Hp', NULL, NULL, NULL, NULL, 850.5, 10.0, 'informática', 1),
('Mouse Logitech', NULL, NULL, NULL, NULL, 25.99, NULL, 'accesorios', 1),
('Teclado Mecánico', NULL, NULL, NULL, NULL, NULL, 25.0, 'accesorios', 1),
(NULL, NULL, NULL, NULL, NULL, 199.99, 5.0, 'monitores', 1),
('Auriculares Sony', NULL, NULL, NULL, NULL, 75.0, -5.0, 'audio', 0),
('Webcam HD', NULL, NULL, NULL, NULL, 45.5, 30.0, NULL, 1),
('Disco SSD 1TB', NULL, NULL, NULL, NULL, 120.0, 15.0, 'almacenamiento', 1),
('Mouse Logitech', NULL, NULL, NULL, NULL, 25.99, 50.0, 'accesorios', 1);
