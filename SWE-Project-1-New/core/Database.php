<?php

/**
 * Database Singleton Class
 * Ensures only one database connection exists throughout the application
 */
class Database
{
    private static ?Database $instance = null;
    private ?mysqli $connection = null;
    
    private string $host;
    private string $user;
    private string $password;
    private string $database;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->host = 'localhost';
        $this->user = 'root';
        $this->password = '';
        $this->database = 'meditrack';
        
        $this->connect();
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Get the singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void
    {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);
        
        if ($this->connection->connect_error) {
            die('Database connection failed: ' . $this->connection->connect_error);
        }
        
        // Set charset to UTF-8
        $this->connection->set_charset("utf8mb4");
    }
    
    /**
     * Get the database connection
     */
    public function getConnection(): mysqli
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Close the database connection
     */
    public function close(): void
    {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Reset instance (useful for testing)
     */
    public static function reset(): void
    {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }
}

