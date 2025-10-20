-- BASE DE DATOS EMPRESARIAL CON ERRORES TÍPICOS
-- Sistema de gestión empresarial con múltiples tablas y datos corruptos

-- TABLA EMPLEADOS (con errores comunes)
INSERT INTO empleados (id, nombre, apellido, email, telefono, departamento, salario, fecha_ingreso, activo) VALUES
(1, 'juan carlos', 'PEREZ MARTINEZ', 'juan@gmai.com', '91234567', 'ventas', '2500.50', '2020-01-15', 'si'),
(2, 'MARIA JOSE', 'garcia lopez', 'maria@hotmial.com', '+34-987-654-321', 'marketing', 'dos mil quinientos', '2019-03-20', 1),
(3, 'Pedro  Antonio', 'López   Ruiz', 'pedro@yahoo.co', '123456789', 'IT', '3000', '2021-05-10', true),
(4, '', 'Martinez Silva', 'ana@gmail.com', '654321987', 'recursos humanos', '2200.75', '2018-04-05', 'activo'),
(5, 'Carlos Miguel', 'Ruiz', 'carlos', '91-111-2222', 'finanzas', '-500', '2022-07-12', 'no'),
(6, 'Sofia Elena', 'Martinez Torres', 'sofia@outlok.com', '', 'ventas', '2800.00', 'ayer', 'enabled'),
(7, 'Luis Fernando', 'Fernandez Diaz', 'luis@gmail.com', '91-555-4444', 'IT', '3500.99', '2023/13/45', 'false'),
(8, 'Ana Cristina', 'Torres Morales', 'ana@gmail.com', '654321987', 'marketing', '2400', '2020-08-30', ''),
(9, 'Roberto Carlos', 'Sanchez Vega', 'roberto@empresa.com', '91-777-8888', 'administracion', '4000.50', '2019-11-15', 'si'),
(10, 'Elena Patricia', 'Moreno Castro', 'elena@gmai.com', '91-999-0000', 'ventas', 'abc', '2021-02-28', 'activo'),
(1, 'juan carlos', 'PEREZ MARTINEZ', 'juan@gmail.com', '91234567', 'ventas', '2500.50', '2020-01-15', 'si'),
(11, 'Diego Alejandro', 'Vargas Herrera', 'diego@hotmial.com', '91-333-4444', 'IT', '3200', '2022-09-10', 'true'),
(12, 'Carmen Rosa', 'Jimenez Flores', 'carmen@yahoo.co', '91-555-6666', 'recursos humanos', '2300.25', '2020-12-05', 'inactivo'),
(13, 'Fernando Jose', 'Castro Mendez', 'fernando@outlok.com', '91-777-9999', 'finanzas', '2900.75', '2021-06-18', 'si'),
(14, 'Lucia Maria', 'Herrera Campos', 'lucia@gmail.comm', '91-111-3333', 'marketing', '2600', '2019-10-22', 'enabled'),
(15, 'Andres Felipe', 'Campos Rojas', 'andres@empresa.com', '91-444-5555', 'administracion', '3800.50', '2022-01-30', 'activo');

-- TABLA PRODUCTOS (con inconsistencias)
INSERT INTO productos (id, nombre, categoria, precio, stock, proveedor_id, fecha_creacion, disponible) VALUES
(101, 'laptop hp pavilion', 'informatica', '850.50', '10', 1, '2023-01-15', 'si'),
(102, 'MOUSE LOGITECH MX', 'accesorios', '25.99', '', 2, '2023-02-20', 1),
(103, 'Teclado Mecánico RGB', 'accesorios', 'cien euros', '25', 1, '2023-03-10', true),
(104, '', 'monitores', '199.99', '5', 3, '2023-04-05', 'disponible'),
(105, 'Auriculares Sony WH', 'audio', '75.00', '-5', 2, '2023-05-12', false),
(106, 'Webcam HD 1080p', 'accesorios', '45.50', '30', 1, 'hoy', 'activo'),
(107, 'Disco SSD 1TB Samsung', 'almacenamiento', '120.00', '15', 3, '2023-07-22', 'si'),
(108, 'Mouse Logitech MX', 'accesorios', '25.99', '50', 2, '2023-08-30', 'disponible'),
(109, 'Monitor 4K Dell', 'monitores', '299.99', '8', 1, '2023-09-15', 'true'),
(110, 'Impresora HP LaserJet', 'oficina', 'doscientos euros', '12', 3, '2023-10-10', 'activo'),
(111, 'Tablet Samsung Galaxy', 'tablets', '180.75', '20', 2, '2023-11-05', 'si'),
(112, 'Smartphone iPhone 14', 'telefonia', '899.99', '3', 1, '2023-12-01', 'disponible'),
(113, 'Cargador USB-C', 'accesorios', '15.25', '100', 2, '2024-01-10', 'activo'),
(114, 'Altavoces Bluetooth', 'audio', '65.00', '25', 3, '2024-02-14', 'si'),
(115, 'Router WiFi 6', 'redes', '89.99', '18', 1, '2024-03-20', 'disponible');

-- TABLA PROVEEDORES (con datos sucios)
INSERT INTO proveedores (id, nombre_empresa, contacto, email, telefono, direccion, ciudad, pais, activo) VALUES
(1, 'TECH SOLUTIONS SL', 'juan martinez', 'info@techsol.com', '91-123-4567', 'Calle Mayor 123', 'madrid', 'españa', 'si'),
(2, 'global electronics', 'MARIA GARCIA', 'ventas@globalelec.com', '+34-987-654-321', 'Av. Libertad 456', 'Barcelona', 'ESPAÑA', 1),
(3, 'Innovación Digital SA', 'Pedro López', 'contacto@innovadigital.es', '91-555-7777', '', 'Valencia', 'España', true),
(4, '', 'Ana Torres', 'ana@proveedores.com', '91-888-9999', 'Plaza Central 789', 'sevilla', 'españa', 'activo'),
(5, 'Suministros Oficina Plus', 'Carlos Ruiz', 'carlos@suministros.com', '', 'Calle Comercio 321', 'Bilbao', 'España', 'no'),
(1, 'TECH SOLUTIONS SL', 'juan martinez', 'info@techsol.com', '91-123-4567', 'Calle Mayor 123', 'madrid', 'españa', 'si'),
(6, 'Distribuidora Nacional', 'Sofia Martinez', 'sofia@distribuidora.es', '91-222-3333', 'Av. Industrial 654', 'Zaragoza', 'España', 'enabled'),
(7, 'Importaciones Europa', 'Luis Fernandez', 'luis@importaciones.eu', '91-444-6666', 'Polígono Sur 987', 'Málaga', 'España', 'activo');

-- TABLA VENTAS (con fechas incorrectas y valores nulos)
INSERT INTO ventas (id, empleado_id, producto_id, cantidad, precio_unitario, descuento, fecha_venta, estado) VALUES
(1001, 1, 101, '2', '850.50', '5.0', '2024-01-15', 'completada'),
(1002, 3, 102, 'cinco', '25.99', '', '2024/13/45', 'pendiente'),
(1003, 5, 103, '1', 'cien', '10.0', 'ayer', 'completada'),
(1004, 2, 104, '3', '199.99', '0', '2024-01-18', 'cancelada'),
(1005, 4, 105, '1', '75.00', '15.5', '2024-01-19', 'completada'),
(1006, 6, 106, '2', '45.50', '', 'hoy', 'pendiente'),
(1007, 1, 107, '1', '120.00', '5.0', '2024-01-22', 'completada'),
(1008, 8, 108, '4', '25.99', '2.5', '2024-01-25', 'completada'),
(1009, 7, 109, '1', '299.99', '20.0', '2024-01-28', 'pendiente'),
(1010, 9, 110, '2', 'doscientos', '0', '2024-02-01', 'completada'),
(1011, 10, 111, '1', '180.75', '8.0', '2024-02-05', 'completada'),
(1012, 11, 112, '1', '899.99', '50.0', '2024-02-10', 'pendiente'),
(1013, 12, 113, '10', '15.25', '5.0', '2024-02-15', 'completada'),
(1014, 13, 114, '3', '65.00', '', '2024-02-20', 'completada'),
(1015, 14, 115, '2', '89.99', '10.0', '2024-02-25', 'pendiente'),
(1001, 1, 101, '2', '850.50', '5.0', '2024-01-15', 'completada');

-- TABLA CLIENTES (con información inconsistente)
INSERT INTO clientes (id, nombre, apellido, email, telefono, direccion, ciudad, codigo_postal, fecha_registro, tipo_cliente, activo) VALUES
(2001, 'alejandro', 'RODRIGUEZ SILVA', 'alex@gmai.com', '91-111-2222', 'Calle Rosales 123', 'madrid', '28001', '2023-01-10', 'premium', 'si'),
(2002, 'BEATRIZ ELENA', 'moreno castro', 'beatriz@hotmial.com', '+34-666-777-888', 'Av. Diagonal 456', 'Barcelona', '08001', '2023-02-15', 'estandar', 1),
(2003, 'Carlos  Eduardo', 'Vega   Herrera', 'carlos@yahoo.co', '91-333-4444', 'Plaza Mayor 789', 'Valencia', '46001', '2023-03-20', 'vip', true),
(2004, '', 'Jimenez Flores', 'carmen@gmail.com', '91-555-6666', 'Calle Libertad 321', 'sevilla', '41001', '2023-04-25', 'premium', 'activo'),
(2005, 'Diego Alejandro', 'Castro Mendez', 'diego', '91-777-8888', 'Av. Constitución 654', 'Bilbao', '48001', '2023-05-30', 'estandar', 'no'),
(2006, 'Elena Patricia', 'Herrera Campos', 'elena@outlok.com', '', 'Calle Comercio 987', 'Zaragoza', '50001', '2023-06-05', 'vip', 'enabled'),
(2007, 'Fernando Jose', 'Campos Rojas', 'fernando@gmail.com', '91-999-0000', 'Plaza Central 147', 'Málaga', '29001', 'ayer', 'premium', 'activo'),
(2008, 'Gloria Maria', 'Rojas Vargas', 'gloria@gmai.com', '91-222-3333', 'Av. Principal 258', 'Córdoba', '14001', '2023-08-15', 'estandar', 'si'),
(2009, 'Hector Luis', 'Vargas Morales', 'hector@hotmial.com', '91-444-5555', 'Calle Nueva 369', 'Granada', '18001', '2023-09-20', 'vip', 'disponible'),
(2010, 'Isabel Carmen', 'Morales Sanchez', 'isabel@yahoo.co', '91-666-7777', 'Av. Libertad 741', 'Salamanca', '37001', '2023-10-25', 'premium', 'activo'),
(2011, 'Javier Antonio', 'Sanchez Torres', 'javier@gmail.comm', '91-888-9999', 'Plaza España 852', 'Valladolid', '47001', '2023-11-30', 'estandar', 'si'),
(2012, 'Laura Sofia', 'Torres Jimenez', 'laura@empresa.com', '91-000-1111', 'Calle Mayor 963', 'Santander', '39001', '2023-12-05', 'vip', 'enabled'),
(2001, 'alejandro', 'RODRIGUEZ SILVA', 'alex@gmail.com', '91-111-2222', 'Calle Rosales 123', 'madrid', '28001', '2023-01-10', 'premium', 'si');

-- TABLA DEPARTAMENTOS (con duplicados y errores)
INSERT INTO departamentos (id, nombre, descripcion, presupuesto, jefe_id, ubicacion, activo) VALUES
(1, 'ventas', 'Departamento de Ventas y Comercial', '50000.00', 1, 'Planta 1', 'si'),
(2, 'MARKETING', 'departamento de marketing digital', 'cuarenta mil', 8, 'Planta 2', 1),
(3, 'IT', 'Tecnologías de la Información', '75000.50', 3, 'Planta 3', true),
(4, 'recursos humanos', 'Gestión de Recursos Humanos', '30000.25', 4, 'Planta 1', 'activo'),
(5, 'finanzas', 'Departamento Financiero y Contable', '-5000', 5, 'Planta 2', 'no'),
(6, 'administracion', 'Administración General', '25000.75', 9, '', 'enabled'),
(1, 'ventas', 'Departamento de Ventas y Comercial', '50000.00', 1, 'Planta 1', 'si'),
(7, 'logistica', 'Logística y Distribución', '35000.00', 11, 'Almacén', 'activo'),
(8, 'calidad', 'Control de Calidad', 'veinte mil', 12, 'Laboratorio', 'si');

-- TABLA INVENTARIO (con stock negativo y fechas incorrectas)
INSERT INTO inventario (id, producto_id, ubicacion, stock_actual, stock_minimo, stock_maximo, fecha_actualizacion, responsable) VALUES
(3001, 101, 'Almacén A', '10', '5', '50', '2024-01-15', 'Juan Perez'),
(3002, 102, 'almacen b', '-3', '10', '100', '2024/13/45', 'MARIA GARCIA'),
(3003, 103, 'Almacén C', 'veinticinco', '5', '30', 'ayer', 'Pedro López'),
(3004, 104, '', '5', '2', '20', '2024-01-18', 'Ana Martinez'),
(3005, 105, 'Almacén A', '-5', '0', '15', 'hoy', 'Carlos Ruiz'),
(3006, 106, 'almacen b', '30', '10', '50', '2024-01-22', ''),
(3007, 107, 'Almacén C', '15', '5', '25', '2024-01-25', 'Luis Fernandez'),
(3008, 108, 'Almacén A', '50', '20', '80', '2024-01-28', 'Ana Torres'),
(3009, 109, 'almacen b', '8', '3', '15', '2024-02-01', 'Roberto Sanchez'),
(3010, 110, 'Almacén C', '12', '5', '20', '2024-02-05', 'Elena Moreno'),
(3011, 111, 'Almacén A', 'veinte', '10', '40', '2024-02-10', 'Diego Vargas'),
(3012, 112, 'almacen b', '3', '1', '10', '2024-02-15', 'Carmen Jimenez'),
(3013, 113, 'Almacén C', '100', '50', '200', '2024-02-20', 'Fernando Castro'),
(3014, 114, 'Almacén A', '25', '10', '50', '2024-02-25', 'Lucia Herrera'),
(3015, 115, 'almacen b', '18', '8', '30', '2024-03-01', 'Andres Campos');

-- TABLA PROYECTOS (con fechas inconsistentes y presupuestos incorrectos)
INSERT INTO proyectos (id, nombre, descripcion, fecha_inicio, fecha_fin, presupuesto, estado, responsable_id, cliente_id) VALUES
(4001, 'sistema crm', 'Implementación Sistema CRM', '2024-01-01', '2024-06-30', '25000.00', 'en progreso', 3, 2001),
(4002, 'CAMPAÑA MARKETING Q1', 'campaña publicitaria primer trimestre', '2024-01-15', '2024/13/45', 'quince mil', 'planificado', 8, 2002),
(4003, 'Migración Servidor', 'Migración a Servidor Cloud', 'ayer', '2024-05-31', '18000.50', 'iniciado', 7, 2003),
(4004, '', 'Auditoría Procesos Internos', '2024-02-01', '2024-04-30', '12000.75', 'completado', 4, 2004),
(4005, 'App Mobile', 'Desarrollo Aplicación Móvil', '2024-03-01', 'mañana', '-5000', 'cancelado', 11, 2005),
(4006, 'Renovación Oficinas', 'Renovación Espacios de Trabajo', '2024-01-10', '2024-03-31', 'treinta mil', 'en progreso', 9, 2006),
(4007, 'Formación Empleados', 'Programa Formación Continua', 'hoy', '2024-12-31', '8000.25', 'planificado', 12, 2007),
(4008, 'Optimización Procesos', 'Mejora Procesos Operativos', '2024-02-15', '2024-07-15', '22000.00', 'iniciado', 13, 2008),
(4009, 'Sistema Facturación', 'Nuevo Sistema de Facturación', '2024-03-01', '2024-08-31', 'veinte mil', 'planificado', 14, 2009),
(4010, 'Expansión Internacional', 'Plan Expansión Mercados', '2024-04-01', '2024-12-31', '50000.99', 'en progreso', 15, 2010);

-- COMENTARIOS ADICIONALES CON ERRORES SINTÁCTICOS
/* Este es un comentario mal cerrado
INSERT INTO test_table VALUES (1, 'test');

-- Más datos con problemas diversos
SELCT * FROM empleados WHERE activo = 'si'; -- Error de sintaxis
INSRT INTO productos VALUES (999, 'Producto Test'); -- Error de sintaxis

-- Fin del archivo con errores típicos empresariales