<?php
class AuthController
{
    private $auth;
    private $apiResponse;
    public function __construct(Auth $auth, ApiResponse $apiResponse)
    {
        $this->auth = $auth;
        $this->apiResponse = $apiResponse;
    }
    public function register(): void
    {
        $input = $this->apiResponse->getJsonInput();
        if (!$input || !isset($input['name'], $input['email'], $input['password'])) {
            $this->apiResponse->error('DonnÃ©es invalides', 400);
        }
        // VÃ©rifier si l'utilisateur existe
        if (User::getByEmail($input['email'])) {
            $this->apiResponse->error('Email dÃ©jÃ  utilisÃ©', 409);
        }
        // CrÃ©er l'utilisateur
        User::create($input['name'], $input['email'], $input['password']);
        // RÃ©cupÃ©rer l'utilisateur crÃ©Ã©
        $user = User::getByEmail($input['email']);
        $token = $this->auth->generateToken($user);
        $this->apiResponse->created([
            'user' => $user->toArray(),
            'token' => $token
        ]);
    }
    public function login(): void
    {
        $input = $this->apiResponse->getJsonInput();
        if (!$input || !isset($input['email'], $input['password'])) {
            $this->apiResponse->error('Email et mot de passe requis', 400);
        }
        $user = User::authenticate($input['email'], $input['password']);
        if (!$user) {
            $this->apiResponse->error('Identifiants invalides', 401);
        }
        $token = $this->auth->generateToken($user);
        $this->apiResponse->success([
            'user' => $user->toArray(),
            'token' => $token
        ]);
    }
    public function me(): void
    {
        $tokenData = $this->auth->authenticate();
        if (!$tokenData) {
            $this->apiResponse->unauthorized();
        }
        $user = User::getById($tokenData['user_id']);
        if (!$user) {
            $this->apiResponse->notFound();
        }
        $this->apiResponse->success($user->toArray());
    }
    public function listUsers(): void
    {
        $users = User::getAll();
        $data = array_map(function ($user) {
            return $user->toArray();
        }, $users);
        $this->apiResponse->success($data);
    }
    public function getUser($id): void
    {
        $user = User::getById($id);
        if (!$user) {
            $this->apiResponse->notFound();
        }
        $this->apiResponse->success($user->toArray());
    }
}

