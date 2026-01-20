<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/api/') === 0) {
    $_GET['route'] = $path;
    require_once __DIR__ . '/api/index.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'API uniquement - utilisez /api/*']);
}

