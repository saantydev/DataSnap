<?php
/**
 * Clase ErrorHandler - Sistema centralizado de manejo de errores
 *
 * Esta clase implementa un sistema robusto de manejo de errores que incluye:
 * - Manejo de errores PHP con códigos estándar
 * - Manejo de excepciones no capturadas
 * - Logging detallado de errores
 * - Respuestas apropiadas según el tipo de error
 * - Modo de depuración para desarrollo
 *
 * @package Core
 * @author Sistema Datasnap
 * @version 1.0
 */
namespace Core;

class ErrorHandler
{
    /**
     * Modo de depuración
     * @var bool
     */
    private $debugMode;

    /**
     * Archivo de log de errores
     * @var string
     */
    private $logFile;

    /**
     * Constructor
     *
     * @param bool $debugMode Habilitar modo de depuración
     * @param string $logFile Ruta del archivo de log
     */
    public function __construct(bool $debugMode = false, string $logFile = null)
    {
        $this->debugMode = $debugMode;
        $this->logFile = $logFile ?: __DIR__ . '/../../logs/errors.log';

        $this->ensureLogDirectory();
        $this->registerHandlers();
    }

    /**
     * Registra los manejadores de errores y excepciones
     *
     * @return void
     */
    public function registerHandlers(): void
    {
        // Registrar manejador de errores
        set_error_handler([$this, 'handleError']);

        // Registrar manejador de excepciones
        set_exception_handler([$this, 'handleException']);

        // Registrar manejador de errores fatales
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Maneja errores PHP
     *
     * @param int $errno Nivel del error
     * @param string $errstr Mensaje del error
     * @param string $errfile Archivo donde ocurrió el error
     * @param int $errline Línea donde ocurrió el error
     * @return bool True si el error fue manejado
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Determinar si el error debe ser reportado
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorData = [
            'type' => 'PHP Error',
            'level' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'context' => $this->getErrorContext($errfile, $errline)
        ];

        $this->logError($errorData);
        $this->respondToError($errorData);

        // No continuar con el manejador de errores interno de PHP
        return true;
    }

    /**
     * Maneja excepciones no capturadas
     *
     * @param \Throwable $exception Excepción no capturada
     * @return void
     */
    public function handleException(\Throwable $exception): void
    {
        $errorData = [
            'type' => 'Uncaught Exception',
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'code' => $exception->getCode()
        ];

        $this->logError($errorData);
        $this->respondToException($errorData);
    }

    /**
     * Maneja errores fatales y de cierre
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null) {
            $errorData = [
                'type' => 'Fatal Error',
                'level' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'context' => $this->getErrorContext($error['file'], $error['line'])
            ];

            $this->logError($errorData);
            $this->respondToFatalError($errorData);
        }
    }

    /**
     * Obtiene el contexto del error (líneas de código alrededor)
     *
     * @param string $file Archivo
     * @param int $line Línea del error
     * @param int $contextLines Número de líneas de contexto
     * @return array Contexto del error
     */
    private function getErrorContext(string $file, int $line, int $contextLines = 5): array
    {
        if (!file_exists($file)) {
            return ['error' => 'Archivo no encontrado'];
        }

        $lines = file($file);
        $start = max(0, $line - $contextLines - 1);
        $end = min(count($lines), $line + $contextLines);

        $context = [];
        for ($i = $start; $i < $end; $i++) {
            $context[] = [
                'line' => $i + 1,
                'code' => rtrim($lines[$i]),
                'is_error_line' => ($i + 1) === $line
            ];
        }

        return $context;
    }

    /**
     * Registra el error en el archivo de log
     *
     * @param array $errorData Datos del error
     * @return void
     */
    private function logError(array $errorData): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$errorData['type']}: {$errorData['message']}\n";
        $logMessage .= "Archivo: {$errorData['file']}:{$errorData['line']}\n";

        if (isset($errorData['level'])) {
            $logMessage .= "Nivel: {$this->getErrorLevelName($errorData['level'])} ({$errorData['level']})\n";
        }

        if (isset($errorData['trace'])) {
            $logMessage .= "Trace:\n{$errorData['trace']}\n";
        }

        if (isset($errorData['context']) && is_array($errorData['context'])) {
            $logMessage .= "Contexto:\n";
            foreach ($errorData['context'] as $contextLine) {
                $marker = $contextLine['is_error_line'] ? '>>>' : '   ';
                $logMessage .= "{$marker} {$contextLine['line']}: {$contextLine['code']}\n";
            }
        }

        $logMessage .= str_repeat('-', 80) . "\n";

        // Crear directorio si no existe
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Obtiene el nombre del nivel de error
     *
     * @param int $level Nivel del error
     * @return string Nombre del nivel
     */
    private function getErrorLevelName(int $level): string
    {
        $errorLevels = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];

        return $errorLevels[$level] ?? 'UNKNOWN';
    }

    /**
     * Responde apropiadamente a un error PHP
     *
     * @param array $errorData Datos del error
     * @return void
     */
    private function respondToError(array $errorData): void
    {
        // Determinar el código HTTP apropiado
        $httpCode = $this->getHttpCodeForError($errorData['level']);

        if ($this->isAjaxRequest()) {
            $this->sendJsonErrorResponse($errorData, $httpCode);
        } else {
            $this->sendHtmlErrorResponse($errorData, $httpCode);
        }
    }

    /**
     * Responde a una excepción no capturada
     *
     * @param array $errorData Datos de la excepción
     * @return void
     */
    private function respondToException(array $errorData): void
    {
        if ($this->isAjaxRequest()) {
            $this->sendJsonErrorResponse($errorData, 500);
        } else {
            $this->sendHtmlErrorResponse($errorData, 500);
        }
    }

    /**
     * Responde a un error fatal
     *
     * @param array $errorData Datos del error fatal
     * @return void
     */
    private function respondToFatalError(array $errorData): void
    {
        // Los errores fatales ya detienen la ejecución, pero podemos intentar enviar una respuesta
        if (!headers_sent()) {
            http_response_code(500);
            if ($this->isAjaxRequest()) {
                $this->sendJsonErrorResponse($errorData, 500);
            } else {
                $this->sendHtmlErrorResponse($errorData, 500);
            }
        }
    }

    /**
     * Obtiene el código HTTP apropiado para un nivel de error
     *
     * @param int $errorLevel Nivel del error
     * @return int Código HTTP
     */
    private function getHttpCodeForError(int $errorLevel): int
    {
        switch ($errorLevel) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 500;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return 400;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            default:
                return 200; // No cambiar el código para notices
        }
    }

    /**
     * Verifica si la solicitud es AJAX
     *
     * @return bool True si es una solicitud AJAX
     */
    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Envía respuesta de error en formato JSON
     *
     * @param array $errorData Datos del error
     * @param int $httpCode Código HTTP
     * @return void
     */
    private function sendJsonErrorResponse(array $errorData, int $httpCode): void
    {
        if (!headers_sent()) {
            http_response_code($httpCode);
            header('Content-Type: application/json');
        }

        $response = [
            'success' => false,
            'error' => [
                'type' => $errorData['type'],
                'message' => $this->debugMode ? $errorData['message'] : 'Ha ocurrido un error interno'
            ]
        ];

        if ($this->debugMode) {
            $response['error']['file'] = $errorData['file'];
            $response['error']['line'] = $errorData['line'];
            if (isset($errorData['trace'])) {
                $response['error']['trace'] = $errorData['trace'];
            }
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Envía respuesta de error en formato HTML
     *
     * @param array $errorData Datos del error
     * @param int $httpCode Código HTTP
     * @return void
     */
    private function sendHtmlErrorResponse(array $errorData, int $httpCode): void
    {
        if (!headers_sent()) {
            http_response_code($httpCode);
        }

        if ($this->debugMode) {
            $this->showDebugErrorPage($errorData);
        } else {
            $this->showUserFriendlyErrorPage($errorData, $httpCode);
        }
        exit();
    }

    /**
     * Muestra página de error para depuración
     *
     * @param array $errorData Datos del error
     * @return void
     */
    private function showDebugErrorPage(array $errorData): void
    {
        echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
        echo "<h1>{$errorData['type']}</h1>";
        echo "<p><strong>Mensaje:</strong> {$errorData['message']}</p>";
        echo "<p><strong>Archivo:</strong> {$errorData['file']}:{$errorData['line']}</p>";

        if (isset($errorData['trace'])) {
            echo "<h2>Stack Trace</h2><pre>{$errorData['trace']}</pre>";
        }

        if (isset($errorData['context'])) {
            echo "<h2>Contexto</h2><pre>";
            foreach ($errorData['context'] as $line) {
                $marker = $line['is_error_line'] ? '>>>' : '   ';
                echo htmlspecialchars("{$marker} {$line['line']}: {$line['code']}\n");
            }
            echo "</pre>";
        }

        echo "</body></html>";
    }

    /**
     * Muestra página de error amigable para el usuario
     *
     * @param array $errorData Datos del error
     * @param int $httpCode Código HTTP
     * @return void
     */
    private function showUserFriendlyErrorPage(array $errorData, int $httpCode): void
    {
        $errorMessages = [
            400 => 'Solicitud incorrecta',
            403 => 'Acceso denegado',
            404 => 'Página no encontrada',
            500 => 'Error interno del servidor'
        ];

        $message = $errorMessages[$httpCode] ?? 'Ha ocurrido un error';

        echo "<!DOCTYPE html><html><head><title>Error {$httpCode}</title></head><body>";
        echo "<h1>Error {$httpCode}</h1>";
        echo "<p>{$message}</p>";
        echo "<p><a href='/'>Volver al inicio</a></p>";
        echo "</body></html>";
    }

    /**
     * Asegura que el directorio de logs existe
     *
     * @return void
     */
    private function ensureLogDirectory(): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Método estático para inicializar el manejador de errores
     *
     * @param bool $debugMode Modo de depuración
     * @return ErrorHandler Instancia del manejador
     */
    public static function init(bool $debugMode = false): ErrorHandler
    {
        return new self($debugMode);
    }
}