<?php

class Database
{
    private static $connection = null;
    private $host = 'php-oop-exercice-db';
    private $db = 'blog';
    private $user = 'root';
    private $password = 'password';

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = (new self())->connect();
        }
        return self::$connection;
    }

    private function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset=UTF8";
            return new PDO($dsn, $this->user, $this->password);
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getLastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}
