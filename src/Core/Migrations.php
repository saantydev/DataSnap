<?php
namespace Core;

class Migrations
{
    public static function run(Database $db): void
    {
        try {
            self::ensureArchivosSchema($db);
        } catch (\Throwable $e) {
            error_log("Migrations::run error: " . $e->getMessage());
        }
    }

    private static function ensureArchivosSchema(Database $db): void
    {
        $pdo = $db->getConnection();
        $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();

        $existing = self::getExistingColumns($pdo, $dbName, 'archivos');

        // Definir columnas requeridas segun el código
        $columns = [
            'nombre'               => "ALTER TABLE archivos ADD COLUMN nombre VARCHAR(255) NULL AFTER ruta",
            'ruta_optimizada'      => "ALTER TABLE archivos ADD COLUMN ruta_optimizada VARCHAR(500) NULL",
            'fecha_optimizacion'   => "ALTER TABLE archivos ADD COLUMN fecha_optimizacion DATETIME NULL",
            'drive_id_original'    => "ALTER TABLE archivos ADD COLUMN drive_id_original VARCHAR(255) NULL",
            'drive_link_original'  => "ALTER TABLE archivos ADD COLUMN drive_link_original VARCHAR(500) NULL",
            'drive_id_optimizado'  => "ALTER TABLE archivos ADD COLUMN drive_id_optimizado VARCHAR(255) NULL",
            'drive_link_optimizado'=> "ALTER TABLE archivos ADD COLUMN drive_link_optimizado VARCHAR(500) NULL",
            'tamano'               => "ALTER TABLE archivos ADD COLUMN tamano BIGINT UNSIGNED NULL",
            'pending_timestamp'    => "ALTER TABLE archivos ADD COLUMN pending_timestamp TIMESTAMP NULL"
        ];

        foreach ($columns as $col => $ddl) {
            if (!in_array($col, $existing, true)) {
                self::safeAlter($db, $ddl, "agregar columna {$col} a 'archivos'");
            }
        }

        // Opcional: asegurar índices básicos si hiciera falta (ya vienen en el script base)
        // self::safeAlter($db, "CREATE INDEX IF NOT EXISTS idx_user_id ON archivos(user_id)", "crear índice idx_user_id");
        // self::safeAlter($db, "CREATE INDEX IF NOT EXISTS idx_estado ON archivos(estado)", "crear índice idx_estado");
        // self::safeAlter($db, "CREATE INDEX IF NOT EXISTS idx_fecha_subida ON archivos(fecha_subida)", "crear índice idx_fecha_subida");
    }

    private static function safeAlter(Database $db, string $sql, string $desc): void
    {
        try {
            $db->query($sql);
            error_log("Migrations: {$desc} aplicado");
        } catch (\Throwable $e) {
            // Si la versión de MySQL no permite IF NOT EXISTS, ignoramos error por duplicado.
            $msg = $e->getMessage();
            $isDuplicate = stripos($msg, 'Duplicate column name') !== false
                        || stripos($msg, 'already exists') !== false;
            if ($isDuplicate) {
                error_log("Migrations: {$desc} ya aplicado anteriormente");
                return;
            }
            error_log("Migrations: fallo al {$desc}: " . $e->getMessage());
        }
    }

    private static function getExistingColumns(\PDO $pdo, string $dbName, string $table): array
    {
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table
        ");
        $stmt->execute([':db' => $dbName, ':table' => $table]);
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return array_map('strtolower', $rows ?: []);
    }
}