<?php
namespace Core;

use Controllers\HomeController;
use Controllers\LoginController;
use Controllers\RegisterController;
use Controllers\FileController;
use Controllers\PanelController;

class Router
{
    private $db;
    private $routes = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->defineRoutes();
        $this->initializeSession();
    }

    private function defineRoutes(): void
    {
        // GET
        $this->routes['GET'] = [
            '/' => ['controller' => 'HomeController', 'method' => 'index'],
            '/login' => ['controller' => 'LoginController', 'method' => 'showLoginForm'],
            '/register' => ['controller' => 'RegisterController', 'method' => 'showRegisterForm'],
            '/forgot-password' => ['controller' => 'PasswordResetController', 'method' => 'showForgotPasswordForm'],
            '/reset-password' => ['controller' => 'PasswordResetController', 'method' => 'showResetPasswordForm'],
            '/panel' => ['controller' => 'PanelController', 'method' => 'index'],
            '/files' => ['controller' => 'FileController', 'method' => 'index'],
            '/files/list' => ['controller' => 'FileController', 'method' => 'list'],
            '/logout' => ['controller' => 'LoginController', 'method' => 'logout'],
            '/auth/google' => ['controller' => 'FileController', 'method' => 'googleAuth'],
        ];

        // POST
        $this->routes['POST'] = [
            '/login' => ['controller' => 'LoginController', 'method' => 'processLogin'],
            '/register' => ['controller' => 'RegisterController', 'method' => 'processRegister'],
            '/forgot-password' => ['controller' => 'PasswordResetController', 'method' => 'processForgotPassword'],
            '/reset-password' => ['controller' => 'PasswordResetController', 'method' => 'processResetPassword'],
            '/files/upload' => ['controller' => 'FileController', 'method' => 'uploadWithDrive'],
            '/files/optimize' => ['controller' => 'FileController', 'method' => 'optimize'],
            '/files/delete' => ['controller' => 'FileController', 'method' => 'delete'],
            '/panel/upload' => ['controller' => 'PanelController', 'method' => 'upload'],
            '/panel/stats' => ['controller' => 'PanelController', 'method' => 'stats'],
            '/auth/google/callback' => ['controller' => 'FileController', 'method' => 'googleCallback'],
        ];

        // Rutas con parámetros
        $this->routes['GET']['/files/download'] = ['controller' => 'FileController', 'method' => 'download'];
    }

    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Lax');

            session_start();
            error_log("Router::initializeSession - Session started with ID: " . session_id());
        } else {
            error_log("Router::initializeSession - Session already active with ID: " . session_id());
        }
    }

    // Entrada principal: resuelve la ruta y ejecuta el controlador.
    public function dispatch(): void
    {
        try {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $requestUri = $this->getCleanUri();

            error_log("Router::dispatch - Method: $requestMethod, URI: $requestUri");
            error_log("Router::dispatch - Session ID: " . session_id());
            error_log("Router::dispatch - Session Status: " . session_status());

            if (isset($this->routes[$requestMethod][$requestUri])) {
                $route = $this->routes[$requestMethod][$requestUri];
                $this->executeController($route['controller'], $route['method']);
                return;
            }

            $params = [];
            foreach ($this->routes[$requestMethod] as $pattern => $route) {
                if ($this->matchRoute($pattern, $requestUri, $params)) {
                    $this->executeController($route['controller'], $route['method'], $params);
                    return;
                }
            }

            $this->handleNotFound();

        } catch (\Exception $e) {
            error_log("Error en Router::dispatch: " . $e->getMessage(), 0);
            $this->handleServerError();
        }
    }

    private function getCleanUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = ltrim($uri, '/');
        return '/' . $uri;
    }

    private function matchRoute(string $pattern, string $uri, array &$params = []): bool
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }

    private function executeController(string $controllerName, string $methodName, array $params = []): void
    {
        try {
            $controller = $this->createController($controllerName);
            if (!method_exists($controller, $methodName)) {
                throw new \Exception("Método '{$methodName}' no encontrado en controlador '{$controllerName}'");
            }
            if (!empty($params)) {
                call_user_func_array([$controller, $methodName], $params);
            } else {
                $controller->$methodName();
            }
        } catch (\Exception $e) {
            error_log("Error ejecutando controlador {$controllerName}::{$methodName}: " . $e->getMessage(), 0);
            $this->handleServerError();
        }
    }

    private function createController(string $controllerName): object
    {
        $controllerClass = "Controllers\\{$controllerName}";
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador '{$controllerClass}' no encontrado");
        }
        return $controllerClass::create($this->db);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        require_once __DIR__ . '/../Views/errors/404.php';
        exit();
    }

    private function handleServerError(): void
    {
        http_response_code(500);
        require_once __DIR__ . '/../Views/errors/500.php';
        exit();
    }

    public static function handleBadRequest(): void
    {
        http_response_code(400);
        require_once __DIR__ . '/../Views/errors/400.php';
        exit();
    }

    // Permite registrar rutas nuevas en runtime.
    public function addRoute(string $method, string $route, string $controller, string $methodName): void
    {
        $this->routes[strtoupper($method)][$route] = [
            'controller' => $controller,
            'method' => $methodName
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public static function init(Database $db): Router
    {
        return new self($db);
    }
}