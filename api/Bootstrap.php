<?php
class Bootstrap
{
    private static $container = [];
    public static function run(): void
    {
        self::setupAutoloader();
        self::setupCors();
        self::setupErrorHandling();
        self::setupServices();
        self::setupRoutes();
        $router = self::getService('router');
        $router->dispatch();
    }
    private static function setupAutoloader(): void
    {
        spl_autoload_register(function ($class_name) {
            $file = __DIR__ . '/' . $class_name . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
    private static function setupCors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 3600');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    private static function setupErrorHandling(): void
    {
        error_reporting(E_ALL);
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            error_log("Error: $errstr in $errfile on line $errline");
        });
    }
    private static function setupServices(): void
    {
        // Services de base
        self::$container['router'] = new Router();
        self::$container['apiResponse'] = new ApiResponse(self::$container['router']);
        self::$container['auth'] = new Auth();
        // ContrÃ´leurs
        self::$container['authController'] = new AuthController(
            self::$container['auth'],
            self::$container['apiResponse']
        );
        self::$container['postController'] = new PostController(
            self::$container['auth'],
            self::$container['apiResponse']
        );
        self::$container['commentController'] = new CommentController(
            self::$container['auth'],
            self::$container['apiResponse']
        );
    }
    private static function setupRoutes(): void
    {
        $router = self::getService('router');
        $authCtrl = self::getService('authController');
        $postCtrl = self::getService('postController');
        $commentCtrl = self::getService('commentController');
        // Routes d'authentification
        $router->post('/api/auth/register', [$authCtrl, 'register']);
        $router->post('/api/auth/login', [$authCtrl, 'login']);
        $router->get('/api/auth/me', [$authCtrl, 'me']);
        // Routes utilisateurs
        $router->get('/api/users', [$authCtrl, 'listUsers']);
        $router->get('/api/users/{id}', [$authCtrl, 'getUser']);
        // Routes articles
        $router->get('/api/posts', [$postCtrl, 'list']);
        $router->get('/api/posts/{id}', [$postCtrl, 'getPost']);
        $router->post('/api/posts', [$postCtrl, 'create']);
        $router->put('/api/posts/{id}', [$postCtrl, 'update']);
        $router->delete('/api/posts/{id}', [$postCtrl, 'delete']);
        $router->get('/api/users/{userId}/posts', [$postCtrl, 'getByUser']);
        // Routes commentaires
        $router->get('/api/posts/{postId}/comments', [$commentCtrl, 'getByPost']);
        $router->get('/api/comments/{id}', [$commentCtrl, 'getComment']);
        $router->post('/api/posts/{postId}/comments', [$commentCtrl, 'create']);
        $router->put('/api/comments/{id}', [$commentCtrl, 'update']);
        $router->delete('/api/comments/{id}', [$commentCtrl, 'delete']);
        $router->get('/api/users/{userId}/comments', [$commentCtrl, 'getByUser']);
    }
    public static function getService(string $name)
    {
        if (!isset(self::$container[$name])) {
            throw new Exception("Service '$name' non trouvÃ© dans le container");
        }
        return self::$container[$name];
    }
    public static function setService(string $name, $service): void
    {
        self::$container[$name] = $service;
    }
}

