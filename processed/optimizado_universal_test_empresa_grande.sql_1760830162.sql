-- Tabla empleados optimizada por IA UNIVERSAL
INSERT INTO empleados (id, nombre, apellido, email, telefono, departamento, salario, fecha_ingreso, activo) VALUES
(1.0, 'Juan Carlos', 'Perez Martinez', 'juan@gmail.com', '91234567', 'ventas', 2500.5, '2020-01-15', 1.0),
(2.0, 'Maria Jose', 'Garcia Lopez', 'maria@hotmail.com', '+34-987-654-321', 'marketing', NULL, '2019-03-20', 1.0),
(3.0, 'Pedro Antonio', 'López Ruiz', 'pedro@yahoo.com', '123456789', 'it', 3000.0, '2021-05-10', 1.0),
(4.0, NULL, 'Martinez Silva', 'ana@gmail.com', '654321987', 'recursos humanos', 2200.75, '2018-04-05', 1.0),
(5.0, 'Carlos Miguel', 'Ruiz', 'carlos@gmail.com', '91-111-2222', 'finanzas', 500.0, '2022-07-12', 0.0),
(6.0, 'Sofia Elena', 'Martinez Torres', 'sofia@outlook.com', NULL, 'ventas', 2800.0, '2024-01-14', 1.0),
(7.0, 'Luis Fernando', 'Fernandez Diaz', 'luis@gmail.com', '91-555-4444', 'it', 3500.99, '2023/13/45', 0.0),
(8.0, 'Ana Cristina', 'Torres Morales', 'ana@gmail.com', '654321987', 'marketing', 2400.0, '2020-08-30', NULL),
(9.0, 'Roberto Carlos', 'Sanchez Vega', 'roberto@empresa.com', '91-777-8888', 'administracion', 4000.5, '2019-11-15', 1.0),
(10.0, 'Elena Patricia', 'Moreno Castro', 'elena@gmail.com', '91-999-0000', 'ventas', NULL, '2021-02-28', 1.0),
(11.0, 'Diego Alejandro', 'Vargas Herrera', 'diego@hotmail.com', '91-333-4444', 'it', 3200.0, '2022-09-10', 1.0),
(12.0, 'Carmen Rosa', 'Jimenez Flores', 'carmen@yahoo.com', '91-555-6666', 'recursos humanos', 2300.25, '2020-12-05', 0.0),
(13.0, 'Fernando Jose', 'Castro Mendez', 'fernando@outlook.com', '91-777-9999', 'finanzas', 2900.75, '2021-06-18', 1.0),
(14.0, 'Lucia Maria', 'Herrera Campos', 'lucia@gmail.com', '91-111-3333', 'marketing', 2600.0, '2019-10-22', 1.0),
(15.0, 'Andres Felipe', 'Campos Rojas', 'andres@empresa.com', '91-444-5555', 'administracion', 3800.5, '2022-01-30', 1.0);

-- Tabla productos optimizada por IA UNIVERSAL
INSERT INTO productos (id, nombre, categoria, precio, stock, proveedor_id, fecha_creacion, disponible) VALUES
(101.0, 'Laptop Hp Pavilion', 'informática', 850.5, 10.0, 1.0, '2023-01-15', 1.0),
(102.0, 'Mouse Logitech Mx', 'accesorios', 25.99, NULL, 2.0, '2023-02-20', 1.0),
(103.0, 'Teclado Mecánico RGB', 'accesorios', NULL, 25.0, 1.0, '2023-03-10', 1.0),
(104.0, NULL, 'monitores', 199.99, 5.0, 3.0, '2023-04-05', NULL),
(105.0, 'Auriculares Sony WH', 'audio', 75.0, 0.0, 2.0, '2023-05-12', 0.0),
(106.0, 'Webcam HD 1080p', 'accesorios', 45.5, 30.0, 1.0, '2025-10-18', 1.0),
(107.0, 'Disco SSD 1TB Samsung', 'almacenamiento', 120.0, 15.0, 3.0, '2023-07-22', 1.0),
(108.0, 'Mouse Logitech MX', 'accesorios', 25.99, 50.0, 2.0, '2023-08-30', NULL),
(109.0, 'Monitor 4K Dell', 'monitores', 299.99, 8.0, 1.0, '2023-09-15', 1.0),
(110.0, 'Impresora HP LaserJet', 'oficina', NULL, 12.0, 3.0, '2023-10-10', 1.0),
(111.0, 'Tablet Samsung Galaxy', 'tablets', 180.75, 20.0, 2.0, '2023-11-05', 1.0),
(112.0, 'Smartphone iPhone 14', 'telefonia', 899.99, 3.0, 1.0, '2023-12-01', NULL),
(113.0, 'Cargador USB-C', 'accesorios', 15.25, 100.0, 2.0, '2024-01-10', 1.0),
(114.0, 'Altavoces Bluetooth', 'audio', 65.0, 25.0, 3.0, '2024-02-14', 1.0),
(115.0, 'Router WiFi 6', 'redes', 89.99, 18.0, 1.0, '2024-03-20', NULL);

-- Tabla proveedores optimizada por IA UNIVERSAL
INSERT INTO proveedores (id, email, telefono, activo, nombre_empresa, contacto, direccion, ciudad, pais) VALUES
(1.0, 'info@techsol.com', '91-123-4567', 1, 'Tech Solutions Sl', 'juan martinez', 'Calle Mayor 123', 'madrid', 'españa'),
(2.0, 'ventas@globalelec.com', '+34-987-654-321', 1, 'Global Electronics', 'MARIA GARCIA', 'Av. Libertad 456', 'Barcelona', 'ESPAÑA'),
(3.0, 'contacto@innovadigital.es', '91-555-7777', 1, 'Innovación Digital SA', 'Pedro López', NULL, 'Valencia', 'España'),
(4.0, 'ana@proveedores.com', '91-888-9999', 1, NULL, 'Ana Torres', 'Plaza Central 789', 'sevilla', 'españa'),
(5.0, 'carlos@suministros.com', NULL, 0, 'Suministros Oficina Plus', 'Carlos Ruiz', 'Calle Comercio 321', 'Bilbao', 'España'),
(6.0, 'sofia@distribuidora.es', '91-222-3333', 1, 'Distribuidora Nacional', 'Sofia Martinez', 'Av. Industrial 654', 'Zaragoza', 'España'),
(7.0, 'luis@importaciones.eu', '91-444-6666', 1, 'Importaciones Europa', 'Luis Fernandez', 'Polígono Sur 987', 'Málaga', 'España');

-- Tabla ventas optimizada por IA UNIVERSAL
INSERT INTO ventas (id, empleado_id, producto_id, cantidad, precio_unitario, descuento, fecha_venta) VALUES
(1001.0, 1.0, 101.0, 2.0, 850.5, 5.0, '2024-01-15');

-- Tabla clientes optimizada por IA UNIVERSAL
INSERT INTO clientes (id, nombre, apellido, email, telefono, activo, direccion, ciudad, codigo_postal, fecha_registro, tipo_cliente) VALUES
(2001.0, 'Alejandro', 'Rodriguez Silva', 'alex@gmail.com', '91-111-2222', 1.0, 'Calle Rosales 123', 'madrid', 28001.0, '2023-01-10', 'premium'),
(2002.0, 'Beatriz Elena', 'Moreno Castro', 'beatriz@hotmail.com', '+34-666-777-888', 1.0, 'Av. Diagonal 456', 'Barcelona', 8001.0, '2023-02-15', 'estandar'),
(2003.0, 'Carlos Eduardo', 'Vega Herrera', 'carlos@yahoo.com', '91-333-4444', 1.0, 'Plaza Mayor 789', 'Valencia', 46001.0, '2023-03-20', 'vip'),
(2004.0, NULL, 'Jimenez Flores', 'carmen@gmail.com', '91-555-6666', 1.0, 'Calle Libertad 321', 'sevilla', 41001.0, '2023-04-25', 'premium'),
(2005.0, 'Diego Alejandro', 'Castro Mendez', 'diego@gmail.com', '91-777-8888', 0.0, 'Av. Constitución 654', 'Bilbao', 48001.0, '2023-05-30', 'estandar'),
(2006.0, 'Elena Patricia', 'Herrera Campos', 'elena@outlook.com', NULL, 1.0, 'Calle Comercio 987', 'Zaragoza', 50001.0, '2023-06-05', 'vip'),
(2007.0, 'Fernando Jose', 'Campos Rojas', 'fernando@gmail.com', '91-999-0000', 1.0, 'Plaza Central 147', 'Málaga', 29001.0, '2024-01-14', 'premium'),
(2008.0, 'Gloria Maria', 'Rojas Vargas', 'gloria@gmail.com', '91-222-3333', 1.0, 'Av. Principal 258', 'Córdoba', 14001.0, '2023-08-15', 'estandar'),
(2009.0, 'Hector Luis', 'Vargas Morales', 'hector@hotmail.com', '91-444-5555', NULL, 'Calle Nueva 369', 'Granada', 18001.0, '2023-09-20', 'vip'),
(2010.0, 'Isabel Carmen', 'Morales Sanchez', 'isabel@yahoo.com', '91-666-7777', 1.0, 'Av. Libertad 741', 'Salamanca', 37001.0, '2023-10-25', 'premium'),
(2011.0, 'Javier Antonio', 'Sanchez Torres', 'javier@gmail.com', '91-888-9999', 1.0, 'Plaza España 852', 'Valladolid', 47001.0, '2023-11-30', 'estandar'),
(2012.0, 'Laura Sofia', 'Torres Jimenez', 'laura@empresa.com', '91-000-1111', 1.0, 'Calle Mayor 963', 'Santander', 39001.0, '2023-12-05', 'vip');

-- Tabla departamentos optimizada por IA UNIVERSAL
INSERT INTO departamentos (id, nombre, activo, descripcion, presupuesto, jefe_id, ubicacion) VALUES
(1.0, 'Ventas', 1, 'Departamento de Ventas y Comercial', '50000.00', 1.0, 'Planta 1'),
(2.0, 'Marketing', 1, 'departamento de marketing digital', 'cuarenta mil', 8.0, 'Planta 2'),
(3.0, 'It', 1, 'Tecnologías de la Información', '75000.50', 3.0, 'Planta 3'),
(4.0, 'Recursos Humanos', 1, 'Gestión de Recursos Humanos', '30000.25', 4.0, 'Planta 1'),
(5.0, 'Finanzas', 0, 'Departamento Financiero y Contable', '-5000', 5.0, 'Planta 2'),
(6.0, 'Administracion', 1, 'Administración General', '25000.75', 9.0, NULL),
(7.0, 'Logistica', 1, 'Logística y Distribución', '35000.00', 11.0, 'Almacén'),
(8.0, 'Calidad', 1, 'Control de Calidad', 'veinte mil', 12.0, 'Laboratorio');

-- Tabla inventario optimizada por IA UNIVERSAL
INSERT INTO inventario (id, producto_id, ubicacion, stock_actual, stock_minimo, stock_maximo, fecha_actualizacion, responsable) VALUES
(3001.0, 101.0, 'Almacén A', 10.0, 5, 50, '2024-01-15', 'Juan Perez');

-- Tabla proyectos optimizada por IA UNIVERSAL
INSERT INTO proyectos (id, nombre, descripcion, presupuesto, fecha_inicio, fecha_fin, responsable_id, cliente_id) VALUES
(4001.0, 'Sistema Crm', 'Implementación Sistema CRM', '25000.00', '2024-01-01', '2024-06-30', 3.0, 2001.0),
(4002.0, 'Campaña Marketing Q1', 'campaña publicitaria primer trimestre', 'quince mil', '2024-01-15', '2024-01-15', 8.0, 2002.0),
(4003.0, 'Migración Servidor', 'Migración a Servidor Cloud', '18000.50', '2024-01-14', '2024-05-31', 7.0, 2003.0),
(4004.0, NULL, 'Auditoría Procesos Internos', '12000.75', '2024-02-01', '2024-04-30', 4.0, 2004.0),
(4005.0, 'App Mobile', 'Desarrollo Aplicación Móvil', '-5000', '2024-03-01', 'mañana', 11.0, 2005.0),
(4006.0, 'Renovación Oficinas', 'Renovación Espacios de Trabajo', 'treinta mil', '2024-01-10', '2024-03-31', 9.0, 2006.0),
(4007.0, 'Formación Empleados', 'Programa Formación Continua', '8000.25', '2025-10-18', '2024-12-31', 12.0, 2007.0),
(4008.0, 'Optimización Procesos', 'Mejora Procesos Operativos', '22000.00', '2024-02-15', '2024-07-15', 13.0, 2008.0),
(4009.0, 'Sistema Facturación', 'Nuevo Sistema de Facturación', 'veinte mil', '2024-03-01', '2024-08-31', 14.0, 2009.0),
(4010.0, 'Expansión Internacional', 'Plan Expansión Mercados', '50000.99', '2024-04-01', '2024-12-31', 15.0, 2010.0);

-- Tabla test_table optimizada por IA UNIVERSAL
INSERT INTO test_table (col_1, col_2) VALUES
(1.0, 'test');
