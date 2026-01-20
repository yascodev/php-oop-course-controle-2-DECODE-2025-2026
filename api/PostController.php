<?php
class PostController
{
    private $auth;
    private $apiResponse;
    public function __construct(Auth $auth, ApiResponse $apiResponse)
    {
        $this->auth = $auth;
        $this->apiResponse = $apiResponse;
    }
    public function list(): void
    {
        $page = (int)$this->apiResponse->getQueryParam('page', 1);
        $limit = (int)$this->apiResponse->getQueryParam('limit', 10);
        $posts = Post::getAll($page, $limit);
        $data = array_map(function ($post) {
            return $post->toArrayWithAuthor();
        }, $posts);
        $this->apiResponse->success($data);
    }
    public function getPost($id): void
    {
        $post = Post::getById($id);
        if (!$post) {
            $this->apiResponse->notFound();
        }
        $this->apiResponse->success($post->toArrayWithAuthor());
    }
    public function create(): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $input = $this->apiResponse->getJsonInput();
        if (!$input || !isset($input['title'], $input['content'])) {
            $this->apiResponse->error('Titre et contenu requis', 400);
        }
        Post::create($tokenData['user_id'], $input['title'], $input['content']);
        $posts = Post::getByUserId($tokenData['user_id']);
        $post = end($posts);
        $this->apiResponse->created($post->toArrayWithAuthor());
    }
    public function update($id): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $post = Post::getById($id);
        if (!$post) {
            $this->apiResponse->notFound();
        }
        // VÃ©rifier que c'est l'auteur
        if ($post->getUserId() !== $tokenData['user_id']) {
            $this->apiResponse->forbidden();
        }
        $input = $this->apiResponse->getJsonInput();
        $title = $input['title'] ?? $post->getTitle();
        $content = $input['content'] ?? $post->getContent();
        $post->update($title, $content);
        $this->apiResponse->success($post->toArrayWithAuthor());
    }
    public function delete($id): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $post = Post::getById($id);
        if (!$post) {
            $this->apiResponse->notFound();
        }
        // VÃ©rifier que c'est l'auteur
        if ($post->getUserId() !== $tokenData['user_id']) {
            $this->apiResponse->forbidden();
        }
        $post->delete();
        $this->apiResponse->success(['message' => 'Article supprimÃ©'], 204);
    }
    public function getByUser($userId): void
    {
        $posts = Post::getByUserId($userId);
        $data = array_map(function ($post) {
            return $post->toArrayWithAuthor();
        }, $posts);
        $this->apiResponse->success($data);
    }
}

