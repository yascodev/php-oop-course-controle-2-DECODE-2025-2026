<?php
class CommentController
{
    private $auth;
    private $apiResponse;
    public function __construct(Auth $auth, ApiResponse $apiResponse)
    {
        $this->auth = $auth;
        $this->apiResponse = $apiResponse;
    }
    public function getByPost($postId): void
    {
        $post = Post::getById($postId);
        if (!$post) {
            $this->apiResponse->notFound();
        }
        $comments = Comment::getByPostId($postId);
        $data = array_map(function ($comment) {
            return $comment->toArrayWithAuthor();
        }, $comments);
        $this->apiResponse->success($data);
    }
    public function getComment($id): void
    {
        $comment = Comment::getById($id);
        if (!$comment) {
            $this->apiResponse->notFound();
        }
        $this->apiResponse->success($comment->toArrayWithAuthor());
    }
    public function create($postId): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $post = Post::getById($postId);
        if (!$post) {
            $this->apiResponse->notFound();
        }
        $input = $this->apiResponse->getJsonInput();
        if (!$input || !isset($input['content'])) {
            $this->apiResponse->error('Contenu du commentaire requis', 400);
        }
        Comment::create($postId, $tokenData['user_id'], $input['content']);
        $comments = Comment::getByPostId($postId);
        $comment = end($comments);
        $this->apiResponse->created($comment->toArrayWithAuthor());
    }
    public function update($id): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $comment = Comment::getById($id);
        if (!$comment) {
            $this->apiResponse->notFound();
        }
        // VÃ©rifier que c'est l'auteur
        if ($comment->getUserId() !== $tokenData['user_id']) {
            $this->apiResponse->forbidden();
        }
        $input = $this->apiResponse->getJsonInput();
        if (!$input || !isset($input['content'])) {
            $this->apiResponse->error('Contenu requis', 400);
        }
        $comment->update($input['content']);
        $this->apiResponse->success($comment->toArrayWithAuthor());
    }
    public function delete($id): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $comment = Comment::getById($id);
        if (!$comment) {
            $this->apiResponse->notFound();
        }
        // VÃ©rifier que c'est l'auteur
        if ($comment->getUserId() !== $tokenData['user_id']) {
            $this->apiResponse->forbidden();
        }
        $comment->delete();
        $this->apiResponse->success(['message' => 'Commentaire supprimÃ©'], 204);
    }
    public function getByUser($userId): void
    {
        $comments = Comment::getByUserId($userId);
        $data = array_map(function ($comment) {
            return $comment->toArrayWithAuthor();
        }, $comments);
        $this->apiResponse->success($data);
    }
}

