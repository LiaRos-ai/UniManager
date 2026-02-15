<?php
declare(strict_types = 1)
;

/**
 * Configuración de base de datos
 */
class DatabaseConfig
{
    public function __construct(private
        string $host = 'localhost', private
        string $database = 'unimanager', private
        string $username = 'root', private
        string $password = '', private
        string $charset = 'utf8mb4', private
        int $port = 3306
        )
    {
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
