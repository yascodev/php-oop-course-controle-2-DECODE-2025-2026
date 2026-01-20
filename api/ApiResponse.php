<?php
class ApiResponse
{
    private $router;
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    public function success($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
    public function error(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
    public function created($data): void
    {
        $this->success($data, 201);
    }
    public function getJsonInput(): ?array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    public function getQueryParam(string $param, $default = null)
    {
        return $_GET[$param] ?? $default;
    }
    public function getHeader(string $name, $default = null)
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$name] ?? $default;
    }
    public function unauthorized(): void
    {
        $this->error('Authentification requise', 401);
    }
    public function forbidden(): void
    {
        $this->error('AccÃ¨s refusÃ©', 403);
    }
    public function notFound(): void
    {
        $this->error('Ressource non trouvÃ©e', 404);
    }
}

