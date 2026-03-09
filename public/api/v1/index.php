<?php
declare(strict_types=1);

// Rutas compatibles con Windows/Linux
// __DIR__ = public/api/v1, subir 2 niveles con dirname
$dir = dirname(__DIR__, 2);
if (!file_exists($dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Database.php')) {
    // Fallback: dirname solo subió 1 nivel, subir 1 más
    $dir = dirname($dir);
}
$projectRoot = $dir;

require_once $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Database.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/EstudiantesAPI.php';
// require_once __DIR__ . '/ProductosAPI.php';  // Añadir más recursos aquí

// 1. Configurar headers JSON y CORS
ApiResponse::setHeaders();

// 2. Parsear la URL
// Ejemplo: /api/v1/estudiantes/5  →  ['', 'api', 'v1', 'estudiantes', '5']
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts  = explode('/', trim($uri, '/'));
$method = $_SERVER['REQUEST_METHOD'];

// $parts[0] = 'api'
// $parts[1] = 'v1'
// $parts[2] = recurso  (estudiantes, productos...)
// $parts[3] = id       (opcional)
$recurso = $parts[2] ?? null;
$id      = isset($parts[3]) && is_numeric($parts[3]) ? (int)$parts[3] : null;

// 3. Envolver todo en try-catch para errores inesperados
try {
    match($recurso) {
        'estudiantes' => (new EstudiantesAPI())->handleRequest($method, $id),
        // 'productos' => (new ProductosAPI())->handleRequest($method, $id),
        null          => ApiResponse::error('Recurso no especificado', 400),
        default       => ApiResponse::error("Recurso '$recurso' no existe", 404),
    };
} catch (\PDOException $e) {
    // Error de base de datos
    error_log('DB Error: ' . $e->getMessage());
    ApiResponse::error('Error interno de base de datos', 500);
} catch (\Exception $e) {
    // Cualquier otro error
    error_log('API Error: ' . $e->getMessage());
    ApiResponse::error('Error interno del servidor', 500);
}