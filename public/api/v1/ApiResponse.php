<?php
declare(strict_types=1);

class ApiResponse
{
    public static function setHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function success($data = null, int $status = 200, ?string $message = null): void
    {
        http_response_code($status);
        $payload = ['success' => true];
        if ($message !== null) $payload['message'] = $message;
        if ($data !== null) $payload['data'] = $data;
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message = 'Error', int $status = 400, $details = null): void
    {
        http_response_code($status);
        $payload = ['success' => false, 'message' => $message];
        if ($details !== null) $payload['errors'] = $details;
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
