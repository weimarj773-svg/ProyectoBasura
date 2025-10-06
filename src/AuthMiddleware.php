<?php
require_once __DIR__ . '/lib/jwt.php';
class AuthMiddleware {
    private $config;
    public function __construct($config) { $this->config = $config; }
    public function requireAuth() {
        $token = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) $token = $m[1];
        }
        if (!$token) {
            json_response(['error'=>'token_missing'],401);exit;
        }
        $payload = jwt_decode($token, $this->config['app']['jwt_secret']);
        if (!$payload) { json_response(['error'=>'invalid_token'],401); exit; }
        if (isset($payload['exp']) && $payload['exp'] < time()) { json_response(['error'=>'expired'],401); exit; }
        return $payload;
    }
}
