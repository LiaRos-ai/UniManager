<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

/**
 * Clase para gestionar la conexión a la base de datos
 */
class Database {
    private static ?self $instance = null;
    private PDO $connection;
    private array $config;
    
    private function __construct(array $config = []) {
        $this->config = array_merge([
            'host' => 'localhost',
            'database' => 'unimanager',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'port' => 3306
        ], $config);
        
        $this->connect();
    }
    
    /**
     * Obtiene la instancia singleton
     */
    public static function getInstance(array $config = []): self {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    /**
     * Establece la conexión a la BD
     */
    private function connect(): void {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene la conexión PDO
     */
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    /**
     * Ejecuta una consulta SELECT
     */
    public function query(string $sql, array $params = []): \PDOStatement {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException("Error en consulta: {$e->getMessage()}\nSQL: $sql");
        }
    }
    
    /**
     * Inserta un registro
     */
    public function insert(string $table, array $data): int {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_values($data));
            
            return (int)$this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new PDOException("Error en INSERT: {$e->getMessage()}");
        }
    }
    
    /**
     * Actualiza registros
     */
    public function update(string $table, array $data, string $where, array $params = []): int {
        try {
            $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
            $sql = "UPDATE $table SET $set WHERE $where";
            
            $values = array_merge(array_values($data), $params);
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new PDOException("Error en UPDATE: {$e->getMessage()}");
        }
    }
    
    /**
     * Elimina registros
     */
    public function delete(string $table, string $where, array $params = []): int {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new PDOException("Error en DELETE: {$e->getMessage()}");
        }
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction(): void {
        $this->connection->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     */
    public function commit(): void {
        $this->connection->commit();
    }
    
    /**
     * Revierte una transacción
     */
    public function rollBack(): void {
        $this->connection->rollBack();
    }
    
    /**
     * Prueba la conexión
     */
    public function testConnection(): bool {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
