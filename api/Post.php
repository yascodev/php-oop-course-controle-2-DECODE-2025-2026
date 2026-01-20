<?php
class Post
{
    private $id;
    private $user_id;
    private $title;
    private $content;
    private $created_at;
    private $updated_at;
    public function __construct($id = null, $user_id = null, $title = null, $content = null, $created_at = null, $updated_at = null)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->content = $content;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }
    public static function create(int $user_id, string $title, string $content): bool
    {
        $db = new Database();
        $sql = "INSERT INTO posts (user_id, title, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $db->execute($sql, [$user_id, $title, $content]);
        return true;
    }
    public static function getById(int $id): ?self
    {
        $db = new Database();
        $post = $db->fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
        if (!$post) return null;
        return new self(
            $post['id'],
            $post['user_id'],
            $post['title'],
            $post['content'],
            $post['created_at'],
            $post['updated_at']
        );
    }
    public static function getAll(int $page = 1, int $limit = 10): array
    {
        $db = new Database();
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $posts = $db->fetchAll($sql, [$limit, $offset]);
        return array_map(function ($post) {
            return new self(
                $post['id'],
                $post['user_id'],
                $post['title'],
                $post['content'],
                $post['created_at'],
                $post['updated_at']
            );
        }, $posts);
    }
    public static function getByUserId(int $user_id): array
    {
        $db = new Database();
        $posts = $db->fetchAll("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
        return array_map(function ($post) {
            return new self(
                $post['id'],
                $post['user_id'],
                $post['title'],
                $post['content'],
                $post['created_at'],
                $post['updated_at']
            );
        }, $posts);
    }
    public function update(string $title = null, string $content = null): bool
    {
        $db = new Database();
        $title = $title ?? $this->title;
        $content = $content ?? $this->content;
        $sql = "UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?";
        $db->execute($sql, [$title, $content, $this->id]);
        $this->title = $title;
        $this->content = $content;
        return true;
    }
    public function delete(): bool
    {
        $db = new Database();
        $sql = "DELETE FROM posts WHERE id = ?";
        $db->execute($sql, [$this->id]);
        return true;
    }
    public function toArrayWithAuthor(): array
    {
        $author = User::getById($this->user_id);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => $author ? $author->toArray() : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
}

