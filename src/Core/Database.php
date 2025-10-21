<?php
/**
 * Clase Database - Maneja la conexión a la base de datos MySQL
 *
 * Esta clase implementa el patrón Singleton para asegurar una única conexión
 * a la base de datos durante toda la ejecución de la aplicación.
 * Incluye manejo robusto de errores usando códigos estándar de PHP.
 *
 * @package Core
 * @author Sistema Datasnap
 * @version 1.0
 */
namespace Core;

class Database
{
    /**
     * Instancia única de la clase (patrón Singleton)
     * @var Database|null
     */
    private static $instance = null;

    /**
     * Conexión PDO a la base de datos
     * @var \PDO|null
     */
    private $connection = null;

    /**
     * Configuración de la base de datos
     * @var array
     */
    private $config;

    /**
     * Constructor privado para implementar patrón Singleton
     *
     * @param array $config Configuración de conexión a la BD
     * @throws \Exception Si la configuración es inválida
     */
    private function __construct(array $config)
    {
        // Intentar conectar con la configuración proporcionada, o con fallbacks
        $this->initializeConnection($config);
    }

    /**
     * Inicializa la conexión con mecanismo de fallback
     *
     * @param array $config Configuración inicial
     * @throws \Exception Si no se puede conectar con ninguna configuración
     */
    private function initializeConnection(array $config): void
    {
        $configsToTry = [$config];

        // Si es la configuración por defecto, agregar local como fallback
        if ($this->isDefaultConfig($config)) {
            $localConfigFile = __DIR__ . '/../../config/database.local.php';
            if (file_exists($localConfigFile)) {
                $localConfig = require $localConfigFile;
                if (!empty($localConfig) && is_array($localConfig)) {
                    $configsToTry[] = $localConfig;
                }
            }
        }

        $lastException = null;
        foreach ($configsToTry as $configAttempt) {
            try {
                // Normalizar claves y aplicar defaults antes de validar
                $configAttempt = $this->normalizeConfig($configAttempt);

                $this->validateConfig($configAttempt);
                $this->config = $configAttempt;
                $this->connect();
                return; // Conexión exitosa
            } catch (\Exception $e) {
                $lastException = $e;
                error_log("Fallo al conectar con configuración: " . $e->getMessage());
            }
        }

        // Si llegamos aquí, ninguna configuración funcionó
        throw new \Exception("No se pudo establecer conexión a la base de datos con ninguna configuración disponible. Último error: " . $lastException->getMessage());
    }

    /**
     * Verifica si la configuración es la por defecto (cargada automáticamente)
     *
     * @param array $config
     * @return bool
     */
    private function isDefaultConfig(array $config): bool
    {
        $defaultConfigFile = __DIR__ . '/../../config/database.php';
        if (file_exists($defaultConfigFile)) {
            $defaultConfig = require $defaultConfigFile;
            return $config === $defaultConfig;
        }
        return false;
    }

    /**
     * Valida la configuración de la base de datos
     *
     * @param array $config Configuración a validar
     * @throws \Exception Si falta algún parámetro requerido
     */
    private function validateConfig(array $config): void
    {
        // Requeridos mínimos
        $requiredKeys = ['host', 'dbname', 'username'];

        foreach ($requiredKeys as $key) {
            if (!isset($config[$key]) || $config[$key] === '') {
                throw new \InvalidArgumentException(
                    "Configuración de base de datos incompleta: falta el parámetro '$key'"
                );
            }
        }

        // Defaults tolerantes
        if (!isset($config['password'])) {
            $config['password'] = '';
        }
        if (!isset($config['charset']) || $config['charset'] === '') {
            $config['charset'] = 'utf8mb4';
        }
    }

    /**
     * Normaliza claves alternativas y aplica defaults
     *
     * Acepta alias comunes (database/db -> dbname, user -> username, pass -> password)
     * y completa valores por defecto.
     *
     * @param array $config
     * @return array
     */
    private function normalizeConfig(array $config): array
    {
        // Aliases
        if (isset($config['database']) && !isset($config['dbname'])) {
            $config['dbname'] = $config['database'];
        }
        if (isset($config['db']) && !isset($config['dbname'])) {
            $config['dbname'] = $config['db'];
        }
        if (isset($config['user']) && !isset($config['username'])) {
            $config['username'] = $config['user'];
        }
        if (isset($config['pass']) && !isset($config['password'])) {
            $config['password'] = $config['pass'];
        }

        // Defaults
        if (!isset($config['charset']) || $config['charset'] === '') {
            $config['charset'] = 'utf8mb4';
        }
        if (!isset($config['password'])) {
            $config['password'] = '';
        }

        return $config;
    }

    /**
     * Establece la conexión a la base de datos
     *
     * @throws \Exception Si no se puede conectar a la base de datos
     */
    private function connect(): void
    {
        try {
            // DEBUG: Mostrar configuración que se está usando
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['dbname'],
                $this->config['charset']
            );

            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new \PDO($dsn, $this->config['username'], $this->config['password'], $options);

            // Log de conexión exitosa
            error_log("Conexión a base de datos establecida correctamente", 0);

        } catch (\PDOException $e) {
            $this->handleConnectionError($e);
        }
    }

    /**
     * Maneja errores de conexión a la base de datos
     *
     * @param \PDOException $e Excepción de PDO
     */
    private function handleConnectionError(\PDOException $e): void
    {
        $errorMessage = "Error de conexión a la base de datos: " . $e->getMessage();

        // Log detallado del error
        error_log($errorMessage . " | Código: " . $e->getCode(), 0);

        // Lanzar excepción para que el caller pueda manejar fallback
        throw new \Exception($errorMessage, (int)$e->getCode(), $e);
    }

    /**
     * Obtiene la instancia única de la clase Database (patrón Singleton)
     *
     * @param array $config Configuración de la base de datos
     * @return Database Instancia de la clase Database
     */
    public static function getInstance(array $config = []): Database
    {
        if (self::$instance === null) {
            if (empty($config)) {
                // Cargar configuración por defecto (el constructor manejará los fallbacks)
                $configFile = __DIR__ . '/../../config/database.php';
                if (!file_exists($configFile)) {
                    throw new \RuntimeException("Archivo de configuración de base de datos no encontrado: $configFile");
                }
                $config = require $configFile;
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }


    /**
     * Obtiene la conexión PDO
     *
     * @return \PDO Conexión a la base de datos
     */
    public function getConnection(): \PDO
    {
        if ($this->connection === null) {
            trigger_error("No hay conexión activa a la base de datos", E_USER_WARNING);
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Ejecuta una consulta preparada
     *
     * @param string $sql Consulta SQL
     * @param array $params Parámetros de la consulta
     * @return \PDOStatement Resultado de la consulta
     * @throws \Exception Si hay error en la consulta
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            $this->handleQueryError($e, $sql, $params);
            // Re-lanzar la excepción después del manejo de error
            throw new \Exception("Error en consulta SQL: " . $e->getMessage());
        }
    }

    /**
     * Maneja errores de consultas SQL
     *
     * @param \PDOException $e Excepción de PDO
     * @param string $sql Consulta SQL que falló
     * @param array $params Parámetros de la consulta
     */
    private function handleQueryError(\PDOException $e, string $sql, array $params): void
    {
        $errorMessage = "Error en consulta SQL: " . $e->getMessage();
        $errorDetails = [
            'SQL' => $sql,
            'Params' => $params,
            'Code' => $e->getCode()
        ];

        // Log detallado del error
        error_log($errorMessage . " | Detalles: " . json_encode($errorDetails), 0);

        // Trigger error con código estándar
        trigger_error($errorMessage, E_USER_WARNING);
    }

    /**
     * Obtiene el último ID insertado
     *
     * @return string Último ID insertado
     */
    public function getLastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Inicia una transacción
     *
     * @return bool True si se inició correctamente
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->getConnection()->beginTransaction();
        } catch (\PDOException $e) {
            trigger_error("Error al iniciar transacción: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Confirma una transacción
     *
     * @return bool True si se confirmó correctamente
     */
    public function commit(): bool
    {
        try {
            return $this->getConnection()->commit();
        } catch (\PDOException $e) {
            trigger_error("Error al confirmar transacción: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Revierte una transacción
     *
     * @return bool True si se revirtió correctamente
     */
    public function rollback(): bool
    {
        try {
            return $this->getConnection()->rollback();
        } catch (\PDOException $e) {
            trigger_error("Error al revertir transacción: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Verifica si hay una transacción activa
     *
     * @return bool True si hay transacción activa
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Destructor - cierra la conexión
     */
    public function __destruct()
    {
        $this->connection = null;
        self::$instance = null;
    }

    /**
     * Previene la clonación de la instancia
     */
    private function __clone() {}

    /**
     * Previene la deserialización de la instancia
     */
    public function __wakeup() {}
}