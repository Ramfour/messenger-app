<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'action' => $action];
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch(string $uri, string $method): void
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $controllerClass = 'App\\Controllers\\' . $handler['controller'];
                $action = $handler['action'];

                if (!class_exists($controllerClass)) {
                    $this->abort(500, "Controller {$controllerClass} not found");
                    return;
                }

                $controller = new $controllerClass();
                $controller->$action(...array_values($params));
                return;
            }
        }

        $this->abort(404);
    }

    private function abort(int $code, string $message = 'Not Found'): void
    {
        http_response_code($code);
        echo $message;
    }
}
