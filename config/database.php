<?php
declare(strict_types=1);

/**
 * config/database.php
 * Proporciona Database::getInstance() para compatibilidad con API REST
 * Internamente usa App\Database\Database del namespace
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database as AppDatabase;

// Wrapper: Database::getInstance() delega a AppDatabase::getInstance()
if (!class_exists('Database')) {
    class Database
    {
        public static function getInstance(array $config = []): AppDatabase
        {
            return AppDatabase::getInstance($config);
        }
    }
}

/**
 * DatabaseConfig - Clase legada para compatibilidad
 */
class DatabaseConfig
{
    public function __construct(
        private string $host = 'localhost',
        private string $database = 'unimanager',
        private string $username = 'root',
        private string $password = '',
        private string $charset = 'utf8mb4',
        private int $port = 3306
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getDSN(): string
    {
        return sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $this->host,
            $this->port,
            $this->database,
            $this->charset
        );
    }
}
