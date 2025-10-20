<?php
/* Controlador ChatBot (IA para consultas de bases de datos) */
namespace Controllers;

use Models\UserModel;
use Models\FileModel;
use Core\Database;

class ChatbotController
{
    private $userModel;
    private $fileModel;
    private $db;

    public function __construct(UserModel $userModel, FileModel $fileModel, Database $db)
    {
        $this->userModel = $userModel;
        $this->fileModel = $fileModel;
        $this->db = $db;
    }

    public static function create(Database $db): ChatbotController
    {
        $userModel = new UserModel($db);
        $fileModel = new FileModel($db);
        return new self($userModel, $fileModel, $db);
    }

    /**
     * Muestra la interfaz del chatbot
     */
    public function index(): void
    {
        try {
            if (!$this->isUserLoggedIn()) {
                $this->redirectToLogin();
                return;
            }

            $userId = $this->getCurrentUserId();
            $user = $this->userModel->findById($userId);

            if (!$user) {
                $this->logout();
                $this->redirectToLogin();
                return;
            }

            $userData = [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];

            require_once __DIR__ . '/../Views/chatbot.html';

        } catch (\Exception $e) {
            error_log("Error en ChatbotController::index: " . $e->getMessage(), 0);
            $this->showError('Error interno del servidor');
        }
    }

    /**
     * Procesa consultas del chatbot
     */
    public function query(): void
    {
        try {
            if (!$this->isUserLoggedIn()) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            // Verificar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
                return;
            }

            // Obtener datos JSON
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['message']) || !isset($input['database_id'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos'], 400);
                return;
            }

            $message = trim($input['message']);
            $databaseId = (int)$input['database_id'];
            $userId = $this->getCurrentUserId();

            // Verificar que el archivo pertenece al usuario
            $file = $this->fileModel->getFileById($databaseId, $userId);
            if (!$file) {
                $this->jsonResponse(['success' => false, 'message' => 'Base de datos no encontrada'], 404);
                return;
            }

            // Procesar la consulta con IA (placeholder por ahora)
            $response = $this->processQuery($message, $file);

            $this->jsonResponse([
                'success' => true,
                'response' => $response
            ]);

        } catch (\Exception $e) {
            error_log("Error en ChatbotController::query: " . $e->getMessage(), 0);
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Procesa la consulta usando IA (placeholder)
     */
    private function processQuery(string $message, array $file): string
    {
        // Por ahora, respuestas simuladas basadas en palabras clave
        $message = strtolower($message);
        
        if (strpos($message, 'cuántos') !== false || strpos($message, 'cantidad') !== false) {
            return "Según mi análisis de la base de datos '{$file['nombre']}', hay aproximadamente 150 registros en total. ¿Te gustaría que analice algún campo específico?";
        }
        
        if (strpos($message, 'promedio') !== false || strpos($message, 'media') !== false) {
            return "He calculado los promedios de los campos numéricos en '{$file['nombre']}'. ¿Podrías especificar qué campo te interesa analizar?";
        }
        
        if (strpos($message, 'usuarios') !== false || strpos($message, 'clientes') !== false) {
            return "En la base de datos '{$file['nombre']}' he identificado varios perfiles de usuarios. ¿Te interesa ver los más activos, los más recientes, o algún filtro específico?";
        }
        
        if (strpos($message, 'errores') !== false || strpos($message, 'problemas') !== false) {
            return "He revisado '{$file['nombre']}' y encontré algunos datos que podrían necesitar limpieza. ¿Quieres que te muestre un resumen de las inconsistencias detectadas?";
        }
        
        if (strpos($message, 'hola') !== false || strpos($message, 'ayuda') !== false) {
            return "¡Hola! Estoy aquí para ayudarte a analizar '{$file['nombre']}'. Puedes preguntarme sobre estadísticas, buscar datos específicos, o pedirme que identifique patrones. ¿Qué te gustaría saber?";
        }
        
        // Respuesta genérica
        return "He analizado tu consulta sobre '{$file['nombre']}'. Aunque aún estoy aprendiendo a interpretar consultas complejas, puedo ayudarte con estadísticas básicas, conteos, y análisis de datos. ¿Podrías reformular tu pregunta de manera más específica?";
    }

    private function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    private function logout(): void
    {
        session_unset();
        session_destroy();
    }

    private function redirectToLogin(): void
    {
        header('Location: /login');
        exit();
    }

    private function showError(string $message): void
    {
        http_response_code(500);
        echo "<h1>Error</h1><p>{$message}</p>";
        exit();
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}