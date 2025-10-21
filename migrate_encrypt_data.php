<?php
/**
 * Script de migración para cifrar datos existentes en la base de datos
 * 
 * Este script cifra todos los drive_id y drive_link existentes en la base de datos
 * usando la nueva clase de cifrado. Debe ejecutarse una sola vez después de 
 * implementar el sistema de cifrado.
 * 
 * IMPORTANTE: Hacer backup de la base de datos antes de ejecutar este script.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Core/Encryption.php';

use Core\Database;
use Core\Encryption;

echo "=== MIGRACIÓN DE CIFRADO DE DATOS ===\n";
echo "Este script cifrará todos los datos sensibles existentes.\n";
echo "ASEGÚRATE de haber hecho un backup de la base de datos.\n\n";

// Confirmar ejecución
echo "¿Continuar? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "Migración cancelada.\n";
    exit(0);
}

try {
    // Conectar a la base de datos
    $config = require __DIR__ . '/config/database.php';
    $db = Database::getInstance($config);
    
    echo "Conectado a la base de datos.\n";
    
    // Obtener todos los archivos que tienen datos de Google Drive sin cifrar
    $sql = "SELECT id, user_id, drive_id_original, drive_link_original, drive_id_optimizado, drive_link_optimizado 
            FROM archivos 
            WHERE (drive_id_original IS NOT NULL AND drive_id_original != '') 
               OR (drive_link_original IS NOT NULL AND drive_link_original != '')
               OR (drive_id_optimizado IS NOT NULL AND drive_id_optimizado != '')
               OR (drive_link_optimizado IS NOT NULL AND drive_link_optimizado != '')";
    
    $stmt = $db->query($sql);
    $files = $stmt->fetchAll();
    
    $totalFiles = count($files);
    echo "Encontrados $totalFiles archivos con datos de Google Drive.\n\n";
    
    if ($totalFiles === 0) {
        echo "No hay archivos para migrar.\n";
        exit(0);
    }
    
    $processed = 0;
    $errors = 0;
    
    foreach ($files as $file) {
        echo "Procesando archivo ID: {$file['id']} (Usuario: {$file['user_id']})... ";
        
        try {
            $updates = [];
            $params = [];
            
            // Cifrar drive_id_original si existe
            if (!empty($file['drive_id_original'])) {
                // Verificar si ya está cifrado (los datos cifrados son más largos)
                if (strlen($file['drive_id_original']) < 50) { // Los IDs de Drive son ~44 chars, cifrados son mucho más largos
                    $encrypted = Encryption::encrypt($file['drive_id_original'], $file['user_id']);
                    if ($encrypted) {
                        $updates[] = "drive_id_original = ?";
                        $params[] = $encrypted;
                    }
                }
            }
            
            // Cifrar drive_link_original si existe
            if (!empty($file['drive_link_original'])) {
                // Verificar si ya está cifrado (los links empiezan con https://)
                if (strpos($file['drive_link_original'], 'https://') === 0) {
                    $encrypted = Encryption::encrypt($file['drive_link_original'], $file['user_id']);
                    if ($encrypted) {
                        $updates[] = "drive_link_original = ?";
                        $params[] = $encrypted;
                    }
                }
            }
            
            // Cifrar drive_id_optimizado si existe
            if (!empty($file['drive_id_optimizado'])) {
                if (strlen($file['drive_id_optimizado']) < 50) {
                    $encrypted = Encryption::encrypt($file['drive_id_optimizado'], $file['user_id']);
                    if ($encrypted) {
                        $updates[] = "drive_id_optimizado = ?";
                        $params[] = $encrypted;
                    }
                }
            }
            
            // Cifrar drive_link_optimizado si existe
            if (!empty($file['drive_link_optimizado'])) {
                if (strpos($file['drive_link_optimizado'], 'https://') === 0) {
                    $encrypted = Encryption::encrypt($file['drive_link_optimizado'], $file['user_id']);
                    if ($encrypted) {
                        $updates[] = "drive_link_optimizado = ?";
                        $params[] = $encrypted;
                    }
                }
            }
            
            // Actualizar si hay cambios
            if (!empty($updates)) {
                $updateSql = "UPDATE archivos SET " . implode(', ', $updates) . " WHERE id = ?";
                $params[] = $file['id'];
                
                $updateStmt = $db->query($updateSql, $params);
                
                if ($updateStmt->rowCount() > 0) {
                    echo "✓ Cifrado exitoso\n";
                } else {
                    echo "⚠ Sin cambios\n";
                }
            } else {
                echo "⚠ Ya cifrado o sin datos\n";
            }
            
            $processed++;
            
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n=== RESUMEN DE MIGRACIÓN ===\n";
    echo "Total de archivos: $totalFiles\n";
    echo "Procesados exitosamente: $processed\n";
    echo "Errores: $errors\n";
    
    if ($errors === 0) {
        echo "\n✓ Migración completada exitosamente.\n";
        echo "Todos los datos sensibles han sido cifrados.\n";
    } else {
        echo "\n⚠ Migración completada con errores.\n";
        echo "Revisa los logs para más detalles.\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ Error fatal durante la migración: " . $e->getMessage() . "\n";
    echo "La migración ha sido interrumpida.\n";
    exit(1);
}