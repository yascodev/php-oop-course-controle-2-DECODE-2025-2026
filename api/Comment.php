<?php
class Comment
{
    private $id;
    private $post_id;
    private $user_id;
    private $content;
    private $created_at;
    private $updated_at;
    public function __construct($id = null, $post_id = null, $user_id = null, $content = null, $created_at = null, $updated_at = null)
    {
        $this->id = $id;
        $this->post_id = $post_id;
        $this->user_id = $user_id;
        $this->content = $content;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }
    public static function create(int $post_id, int $user_id, string $content): bool
    {
        $db = new Database();
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $db->execute($sql, [$post_id, $user_id, $content]);
        return true;
    }
    public static function getById(int $id): ?self
    {
        $db = new Database();
        $comment = $db->fetchOne("SELECT * FROM comments WHERE id = ?", [$id]);
        if (!$comment) return null;
        return new self(
            $comment['id'],
            $comment['post_id'],
            $comment['user_id'],
            $comment['content'],
            $comment['created_at'],
            $comment['updated_at']
        );
    }
    public static function getByPostId(int $post_id): array
    {
        $db = new Database();
        $comments = $db->fetchAll("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC", [$post_id]);
        return array_map(function ($comment) {
            return new self(
                $comment['id'],
                $comment['post_id'],
                $comment['user_id'],
                $comment['content'],
                $comment['created_at'],
                $comment['updated_at']
            );
        }, $comments);
    }
    public static function getByUserId(int $user_id): array
    {
        $db = new Database();
        $comments = $db->fetchAll("SELECT * FROM comments WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
        return array_map(function ($comment) {
            return new self(
                $comment['id'],
                $comment['post_id'],
                $comment['user_id'],
                $comment['content'],
                $comment['created_at'],
                $comment['updated_at']
            );
        }, $comments);
    }
    public function update(string $content): bool
    {
        $db = new Database();
        $sql = "UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ?";
        $db->execute($sql, [$content, $this->id]);
        $this->content = $content;
        return true;
    }
    public function delete(): bool
    {
        $db = new Database();
        $sql = "DELETE FROM comments WHERE id = ?";
        $db->execute($sql, [$this->id]);
        return true;
    }
    public function toArrayWithAuthor(): array
    {
        $author = User::getById($this->user_id);
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
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
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    // Getters
    public function getId() { return $this->id; }
    public function getPostId() { return $this->post_id; }
    public function getUserId() { return $this->user_id; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
}

