<?php
class Auth
{
    private $secret = 'php-oop-exercice-secret-key-2025';
    private $algorithm = 'HS256';
    private $expirationTime = 86400; // 24 heures
    public function generateToken(User $user): string
    {
        $header = [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];
        $payload = [
            'iat' => time(),
            'exp' => time() + $this->expirationTime,
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName()
        ];
        return $this->encodeToken($header, $payload);
    }
    private function encodeToken(array $header, array $payload): string
    {
        $header64 = base64_encode(json_encode($header));
        $payload64 = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header64.$payload64", $this->secret, true);
        $signature64 = base64_encode($signature);
        return "$header64.$payload64.$signature64";
    }
    public function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        list($header64, $payload64, $signature64) = $parts;
        // VÃ©rifie la signature
        $expectedSignature = base64_encode(
            hash_hmac('sha256', "$header64.$payload64", $this->secret, true)
        );
        if ($signature64 !== $expectedSignature) {
            return null;
        }
        $payload = json_decode(base64_decode($payload64), true);
        // VÃ©rifie l'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        return $payload;
    }
    public function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }
        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }
    public function authenticate(): ?array
    {
        $token = $this->getTokenFromHeader();
        if (!$token) {
            return null;
        }
        return $this->verifyToken($token);
    }
    public function getSecret(): string
    {
        return $this->secret;
    }
}

