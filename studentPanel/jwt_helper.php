<?php
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    private $secretKey = "your_secret_key_here";

    public function encode($data) {
        return JWT::encode($data, $this->secretKey, 'HS256');
    }

    public function decode($jwt) {
        try {
            return JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
