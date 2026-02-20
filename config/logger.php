<?php
declare(strict_types=1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class AppLogger {
    private static ?Logger $instance = null;
    
    public static function getInstance(): Logger {
        if (self::$instance === null) {
            self::$instance = self::createLogger();
        }
        return self::$instance;
    }
    
    private static function createLogger(): Logger {
        $logger = new Logger('unimanager');
        
        // App logs
        $appHandler = new StreamHandler(
            __DIR__ . '/../logs/app.log',
            Logger::INFO
        );
        
        // Error logs (rotación)
        $errorHandler = new RotatingFileHandler(
            __DIR__ . '/../logs/error.log',
            30,
            Logger::ERROR
        );
        
        $logger->pushHandler($appHandler);
        $logger->pushHandler($errorHandler);
        
        return $logger;
    }
}
?>