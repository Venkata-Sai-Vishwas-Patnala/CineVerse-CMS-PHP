<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Check if database exists
try {
    require_once __DIR__ . '/config/db.php';
    $testDb = getDB();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database not configured',
        'message' => 'Please run setup.php to create the database',
        'setup_url' => '/CineVerse/setup.php'
    ]);
    exit;
}

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = str_replace('/CineVerse/api/', '', $uri);
$uri    = str_replace('/api/', '', $uri);
$parts  = explode('/', trim($uri, '/'));
$resource = $parts[0] ?? '';
$id       = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$action   = isset($parts[1]) && !is_numeric($parts[1]) ? $parts[1] : ($parts[2] ?? null);

$_REQUEST['_id']     = $id;
$_REQUEST['_action'] = $action;

match ($resource) {
    'auth'       => require __DIR__ . '/api/auth.php',
    'movies'     => require __DIR__ . '/api/movies.php',
    'reviews'    => require __DIR__ . '/api/reviews.php',
    'categories' => require __DIR__ . '/api/categories.php',
    'watchlist'  => require __DIR__ . '/api/watchlist.php',
    'upload'     => require __DIR__ . '/api/upload.php',
    'admin'      => require __DIR__ . '/api/admin.php',
    default      => (function() {
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint not found',
            'available_endpoints' => [
                '/api/auth/*',
                '/api/movies/*',
                '/api/reviews/*',
                '/api/categories',
                '/api/watchlist',
                '/api/admin/*',
            ],
            'hint' => 'Make sure database is set up at /CineVerse/setup.php'
        ]);
    })()
};
