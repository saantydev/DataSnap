# DataSnap

DataSnap es una plataforma web integral para el procesamiento y optimización de datos utilizando inteligencia artificial. Permite a los usuarios subir archivos CSV, TXT y JSON, procesarlos automáticamente para corregir errores, eliminar duplicados y optimizar la estructura de datos.

## Características Principales

- **Procesamiento Inteligente de Archivos**: Soporte para CSV, TXT y JSON
- **Corrección Automática de Errores**: Eliminación de datos duplicados, conversión de tipos de datos, limpieza de espacios
- **Optimización de Datos**: Mejora la calidad y estructura de los datasets
- **Sistema de Copias de Seguridad**: Historial automático de versiones procesadas
- **Estadísticas y Análisis**: Informes detallados sobre los datos procesados
- **Interfaz Web Segura**: Autenticación de usuarios, sesiones seguras y protección CSRF

## Arquitectura del Proyecto

El proyecto está dividido en dos componentes principales:

### Backend PHP (MVC)
- **Framework**: PHP nativo con arquitectura MVC personalizada
- **Base de Datos**: MySQL/MariaDB
- **Sesiones**: Manejo seguro de autenticación y sesiones
- **Enrutamiento**: Sistema de rutas personalizado con protección de middleware

### API Python (Flask)
- **Framework**: Flask para procesamiento de datos
- **Librerías**: Pandas, Number Parser para análisis y limpieza de datos
- **Procesamiento**: Algoritmos de IA para optimización automática

## Estructura de Archivos

```
DataSnap/
├── index.php                 # Punto de entrada PHP (MVC)
├── index.html                # Página de landing estática
├── api.py                    # API Flask para procesamiento de datos
├── .htaccess                 # Configuración Apache
├── database_setup.sql        # Script de creación de BD
├── README.md                 # Este archivo
├── src/                      # Código fuente PHP
│   ├── Controllers/          # Controladores MVC
│   ├── Models/               # Modelos de datos
│   ├── Views/                # Vistas HTML/PHP
│   └── Core/                 # Núcleo del framework
├── config/                   # Configuraciones
│   ├── database.php          # Configuración BD
│   └── css/                  # Estilos CSS
├── assets/                   # Recursos estáticos
├── uploads/                  # Archivos subidos por usuarios
└── secure/                   # Credenciales y datos sensibles
```

## Instalación y Configuración

### Requisitos
- PHP 7.4+
- Python 3.8+
- MySQL/MariaDB
- Apache con mod_rewrite
- Librerías Python: flask, pandas, number-parser

### Pasos de Instalación
1. Clonar el repositorio
2. Configurar la base de datos ejecutando `database_setup.sql`
3. Actualizar `config/database.php` con las credenciales de BD
4. Instalar dependencias Python: `pip install flask pandas number-parser`
5. Configurar Apache para apuntar a la raíz del proyecto

### Ejecución
- **Aplicación Web**: Acceder a `index.html` o `index.php` vía navegador
- **API Python**: Ejecutar `python api.py` (puerto 5000)

## Uso

1. **Registro/Login**: Crear cuenta o iniciar sesión
2. **Subir Archivo**: Desde el panel, subir archivo CSV/TXT/JSON
3. **Procesamiento**: La IA procesa automáticamente el archivo
4. **Descarga**: Obtener el archivo optimizado
5. **Estadísticas**: Ver informes de procesamiento

## Seguridad

- Autenticación robusta con hash de contraseñas
- Protección CSRF en formularios
- Sesiones seguras con regeneración de IDs
- Validación de archivos y tipos MIME
- Límites de tamaño de archivos configurables

## Desarrollo

El proyecto sigue buenas prácticas de desarrollo:
- Arquitectura MVC clara
- Separación de responsabilidades
- Manejo de errores centralizado
- Logging detallado
- Código comentado y documentado

## Contribución

Para contribuir:
1. Fork el proyecto
2. Crear rama para feature/fix
3. Commit cambios
4. Push y crear Pull Request

## Licencia

Este proyecto es propiedad de DataSnap. Todos los derechos reservados.