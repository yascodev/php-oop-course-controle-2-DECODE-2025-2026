<?php
class User
{
    private $id;
    private $name;
    private $email;
    private $password;
    private $created_at;
    public function __construct($id = null, $name = null, $email = null, $password = null, $created_at = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->created_at = $created_at;
    }
    public static function create(string $name, string $email, string $password): bool
    {
        $db = new Database();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
        $db->execute($sql, [$name, $email, $hashedPassword]);
        return true;
    }
    public static function getById(int $id): ?self
    {
        $db = new Database();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) return null;
        return new self(
            $user['id'],
            $user['name'],
            $user['email'],
            $user['password'],
            $user['created_at']
        );
    }
    public static function getByEmail(string $email): ?self
    {
        $db = new Database();
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if (!$user) return null;
        return new self(
            $user['id'],
            $user['name'],
            $user['email'],
            $user['password'],
            $user['created_at']
        );
    }
    public static function getByName(string $name): ?self
    {
        $db = new Database();
        $user = $db->fetchOne("SELECT * FROM users WHERE name = ?", [$name]);
        if (!$user) return null;
        return new self(
            $user['id'],
            $user['name'],
            $user['email'],
            $user['password'],
            $user['created_at']
        );
    }
    public static function getAll(): array
    {
        $db = new Database();
        $users = $db->fetchAll("SELECT id, name, email, created_at FROM users");
        return array_map(function ($user) {
            return new self(
                $user['id'],
                $user['name'],
                $user['email'],
                null,
                $user['created_at']
            );
        }, $users);
    }
    public static function authenticate(string $email, string $password): ?self
    {
        $user = self::getByEmail($email);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at
        ];
    }
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getCreatedAt() { return $this->created_at; }
}

