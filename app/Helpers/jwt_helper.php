<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getJWTKey(): string
{
    return config('JWT')->key;
}

function generateJWT(array $user): string
{
    $key = getJWTKey();
    $issuedAt = time();
    $expiresAt = $issuedAt + 28800;

    $payload = [
        'iat' => $issuedAt,
        'exp' => $expiresAt,
        'sub' => $user['id'],
        'role' => $user['role'],
        'username' => $user['username'],
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function validateJWT(string $token): ?object
{
    try {
        $key = getJWTKey();
        return JWT::decode($token, new Key($key, 'HS256'));
    } catch (\Exception $e) {
        return null;
    }
}
