<?php
class Router
{
    private $routes = [];
    private $currentMethod = '';
    private $currentPath = '';
    public function __construct()
    {
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        if (isset($_GET['route'])) {
            $this->currentPath = $_GET['route'];
        } elseif (isset($_SERVER['REDIRECT_URL'])) {
            $this->currentPath = $_SERVER['REDIRECT_URL'];
        } elseif (isset($_SERVER['PATH_INFO'])) {
            $this->currentPath = $_SERVER['PATH_INFO'];
        } else {
            $this->currentPath = $requestUri;
        }
    }
    public function get(string $path, callable $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }
    public function post(string $path, callable $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }
    public function put(string $path, callable $callback): void
    {
        $this->routes['PUT'][$path] = $callback;
    }
    public function delete(string $path, callable $callback): void
    {
        $this->routes['DELETE'][$path] = $callback;
    }
    private function patternToRegex(string $pattern): string
    {
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '/^' . $pattern . '$/';
    }
    private function extractParams(string $pattern, string $path): ?array
    {
        $regex = $this->patternToRegex($pattern);
        if (preg_match($regex, $path, $matches)) {
            return array_filter($matches, function ($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
        }
        return null;
    }
    public function dispatch(): void
    {
        if (!isset($this->routes[$this->currentMethod])) {
            http_response_code(404);
            echo json_encode(['error' => 'MÃ©thode non supportÃ©e']);
            return;
        }
        foreach ($this->routes[$this->currentMethod] as $pattern => $callback) {
            $params = $this->extractParams($pattern, $this->currentPath);
            if ($params !== null) {
                call_user_func_array($callback, array_values($params));
                return;
            }
        }
        http_response_code(404);
        echo json_encode(['error' => 'Route non trouvÃ©e']);
    }
    public function getCurrentPath(): string
    {
        return $this->currentPath;
    }
    public function getCurrentMethod(): string
    {
        return $this->currentMethod;
    }
}

