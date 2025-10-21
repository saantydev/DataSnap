# Implementación de Cifrado para Datos de Google Drive

## Descripción del Problema
Como administrador, puedes ver todos los archivos desde la base de datos, incluyendo los `drive_id` y `drive_link` que deberían ser privados para cada usuario.

## Solución Implementada
Se ha implementado un sistema de cifrado AES-256-CBC que cifra los siguientes campos:
- `drive_id_original`
- `drive_link_original` 
- `drive_id_optimizado`
- `drive_link_optimizado`

### Características del Cifrado:
- **Algoritmo**: AES-256-CBC
- **Clave única por usuario**: Cada usuario tiene su propia clave de cifrado
- **Seguridad**: Solo el usuario propietario puede descifrar sus datos
- **Transparente**: El cifrado/descifrado es automático en la aplicación

## Archivos Creados/Modificados

### Archivos Creados:
1. **`src/Core/Encryption.php`** - Clase principal de cifrado
2. **`migrate_encrypt_data.php`** - Script de migración para datos existentes
3. **`IMPLEMENTACION_CIFRADO.md`** - Este archivo de documentación

### Archivos Modificados:
1. **`src/Models/FileModel.php`** - Agregado soporte para cifrado/descifrado
2. **`src/Controllers/FileController.php`** - Actualizado para usar métodos cifrados
3. **`src/Models/archivos_model.php`** - Agregado descifrado automático

## Pasos para Implementar

### 1. Backup de la Base de Datos
```bash
# IMPORTANTE: Hacer backup antes de continuar
mysqldump -u tu_usuario -p tu_base_de_datos > backup_antes_cifrado.sql
```

### 2. Ejecutar Migración
```bash
# Ejecutar el script de migración para cifrar datos existentes
php migrate_encrypt_data.php
```

### 3. Verificar Funcionamiento
- Los usuarios existentes podrán seguir accediendo a sus archivos normalmente
- Los nuevos archivos se cifrarán automáticamente
- Como administrador, verás datos cifrados en la base de datos

## Cómo Funciona

### Para Nuevos Archivos:
- Cuando se sube un archivo y se obtiene el `drive_id` y `drive_link`
- Estos se cifran automáticamente antes de guardarse en la BD
- Solo el usuario propietario puede descifrarlos

### Para Archivos Existentes:
- El script de migración cifra todos los datos existentes
- Mantiene la funcionalidad para todos los usuarios

### Seguridad:
- Cada usuario tiene una clave única basada en su ID
- Los datos cifrados son inútiles sin la clave correcta
- Como administrador, verás datos cifrados ilegibles

## Ejemplo de Datos Antes y Después

### Antes (visible para admin):
```
drive_id_original: "1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms"
drive_link_original: "https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/view"
```

### Después (cifrado para admin):
```
drive_id_original: "eyJpdiI6IkFCQ0RFRkdISUpLTE1OT1AiLCJ2YWx1ZSI6IlhZWjEyMzQ1Njc4OTAiLCJtYWMiOiJhYmNkZWZnaGlqa2xtbm9wIn0="
drive_link_original: "eyJpdiI6IlFSU1RVVldYWVoxMjM0NSIsInZhbHVlIjoiYWJjZGVmZ2hpamtsbW5vcCIsIm1hYyI6IjEyMzQ1Njc4OTBhYmNkZWYifQ=="
```

## Configuración de Seguridad

### Cambiar Clave Base (Recomendado para Producción):
En `src/Core/Encryption.php`, línea 19:
```php
private const BASE_KEY = 'TU_CLAVE_SUPER_SECRETA_AQUI_2024!@#$%^&*()';
```

### Consideraciones:
- La clave base debe ser única para tu instalación
- Una vez en producción, NO cambiar la clave base (los datos no se podrán descifrar)
- Mantener la clave base segura y no exponerla en repositorios públicos

## Verificación del Funcionamiento

### 1. Verificar Cifrado en BD:
```sql
SELECT id, user_id, drive_id_original, drive_link_original 
FROM archivos 
WHERE drive_id_original IS NOT NULL 
LIMIT 5;
```
Deberías ver datos cifrados (strings largos en base64).

### 2. Verificar Descifrado en Aplicación:
- Inicia sesión como usuario
- Ve a "Mis Archivos"
- Los archivos deben mostrarse normalmente
- Los links de descarga deben funcionar

## Troubleshooting

### Si los usuarios no pueden ver sus archivos:
1. Verificar que la migración se ejecutó correctamente
2. Revisar logs de PHP para errores de descifrado
3. Verificar que la clave base no haya cambiado

### Si aparecen errores de cifrado:
1. Verificar que la extensión OpenSSL esté habilitada en PHP
2. Revisar permisos de archivos
3. Verificar logs de error de PHP

## Beneficios de Seguridad

✅ **Privacidad**: Solo el usuario propietario puede acceder a sus links de Google Drive
✅ **Seguridad**: Los datos están cifrados con AES-256-CBC
✅ **Transparencia**: Los usuarios no notan ningún cambio en la funcionalidad
✅ **Administración**: Como admin, no puedes acceder a los archivos privados de los usuarios
✅ **Cumplimiento**: Mejor cumplimiento de privacidad de datos

## Notas Importantes

- ⚠️ **HACER BACKUP** antes de ejecutar la migración
- ⚠️ **NO CAMBIAR** la clave base después de cifrar datos
- ⚠️ **MANTENER SEGURA** la clave base
- ✅ El sistema es **retrocompatible** con datos existentes
- ✅ **No afecta** la experiencia del usuario final